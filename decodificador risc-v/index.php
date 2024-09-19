<?php

class ConjuntoInstrucao
{
    public $instrucao;
    public $opcode;
    public $rd;
    public $funct3;
    public $rs1;
    public $rs2;
    public $funct7;
    public $tipo;
    public $nop;

    public function toArray()
    {
        return [
            'instrucao' => $this->instrucao,
            'opcode' => $this->opcode,
            'rd' => $this->rd,
            'funct3' => $this->funct3,
            'rs1' => $this->rs1,
            'rs2' => $this->rs2,
            'funct7' => $this->funct7,
            'tipo' => $this->tipo,
            'nop' => $this->nop,
        ];
    }
}

// Definir as categorias de instruções RISC-V
$categories = [
    'total' => 0,
    'alu' => 0,
    'jump' => 0,
    'branch' => 0,
    'memory' => 0,
    'other' => 0
];

// Definir as instruções RISC-V e suas categorias
$alu_instructions = ['0110011', '0010011'];
$jump_instructions = ['1101111', '1100111'];
$branch_instructions = ['1100011'];
$memory_instructions = ['0001111', '1110011'];
$load_instructions = ['0000011'];
$store_instructions = ['0100011'];
function opcode($opcode, $verificarTipo)
{
    global $categories, $alu_instructions, $jump_instructions, $branch_instructions, $memory_instructions, $load_instructions, $store_instructions;

    if (in_array($opcode, $alu_instructions)) {
        if ($verificarTipo) {
            return 'alu';
        }
        $categories['alu']++;
    } elseif (in_array($opcode, $jump_instructions)) {
        if ($verificarTipo) {
            return 'jump';
        }
        $categories['jump']++;
    } elseif (in_array($opcode, $branch_instructions)) {
        if ($verificarTipo) {
            return 'branch';
        }
        $categories['branch']++;
    } elseif (in_array($opcode, $memory_instructions)) {
        if ($verificarTipo) {
            return 'memory';
        }
        $categories['memory']++;
    } elseif (in_array($opcode, $load_instructions)) {
        if ($verificarTipo) {
            return 'load';
        }
        $categories['other']++;
    } elseif (in_array($opcode, $store_instructions)) {
        if ($verificarTipo) {
            return 'store';
        }
        $categories['other']++;
    } else {
        if ($verificarTipo) {
            return 'other';
        }
        $categories['other']++;
    }
    $categories['total']++;
}

// Função para obter o valor binário de um caractere hexadecimal
function obterBinario($c)
{
    $map = [
        '0' => "0000", '1' => "0001", '2' => "0010", '3' => "0011",
        '4' => "0100", '5' => "0101", '6' => "0110", '7' => "0111",
        '8' => "1000", '9' => "1001", 'A' => "1010", 'a' => "1010",
        'B' => "1011", 'b' => "1011", 'C' => "1100", 'c' => "1100",
        'D' => "1101", 'd' => "1101", 'E' => "1110", 'e' => "1110",
        'F' => "1111", 'f' => "1111"
    ];
    return $map[$c] ?? "????";
}

function verificar_hazard_instrucao($instrucao_1, $instrucao_2)
{
    return $instrucao_1['rd'] == $instrucao_2['rs1'] || $instrucao_1['rd'] == $instrucao_2['rs2'];
}

function verificar_hazards($instrucoes)
{
    $hazards = [];
    for ($x = 0; $x < count($instrucoes); $x++) {
        if ($instrucoes[$x]['rs1'] == "rd" && $instrucoes[$x]['tipo'] == "store"){
            continue;
        }
        for ($y = $x + 1; $y < $x + 3; $y++) {
            if ($y >= count($instrucoes)) {
                continue;
            }
            if (verificar_hazard_instrucao($instrucoes[$x], $instrucoes[$y])) {
                $hazards[] = $x;
            }
        }
    }
    return $hazards;
}

function inserir_nops($instrucoes, $hazards, $forwarding)
{
    $no_operator = new ConjuntoInstrucao();
    $no_operator->instrucao = "00000000000000000000000000110011 nop";

    for ($x = count($hazards) - 1; $x >= 0; $x--) {
        $qtd_nops = $forwarding ? 1 : 2;
        for ($y = $hazards[$x] + 1; $y <= $hazards[$x] + $qtd_nops; $y++) {
            if (verificar_hazard_instrucao($instrucoes[$hazards[$x]], $instrucoes[$y])) {
                for ($k = 0; $k < $qtd_nops; $k++) {
                    array_splice($instrucoes, $hazards[$x] + 1, 0, [$no_operator->toArray()]);
                }
                $qtd_nops--;
            }
        }
    }
    return $instrucoes;
}

$inputFile = fopen("lerHex.txt", "r");
$outputFile = fopen("gravar.txt", "w");
$outputFile2 = fopen("convertidoBinario.txt", "w");

if ($inputFile && $outputFile) {
    $conjuntos = [];
    $teste = '';
    while (($instrucao = fgets($inputFile)) !== false) {
        $hexString = trim($instrucao);
        $binaryString = "";
        foreach (str_split($hexString) as $c) {
            $binaryString .= obterBinario($c);
        }
        $teste .= $binaryString . "\n";
        $conjunto = new ConjuntoInstrucao();
        $conjunto->instrucao = $binaryString;
        $conjunto->opcode = substr($binaryString, 25, 7);
        $conjunto->rd = substr($binaryString, 20, 5);
        $conjunto->funct3 = substr($binaryString, 17, 3);
        $conjunto->rs1 = substr($binaryString, 12, 5);
        $conjunto->rs2 = substr($binaryString, 7, 5);
        $conjunto->funct7 = substr($binaryString, 0, 7);
        $conjunto->tipo = opcode($conjunto->opcode, true);
        $conjunto->nop = false;
        opcode($conjunto->opcode, false);
        $conjuntos[] = $conjunto->toArray();
    }
    $forwarding = true;
    $hazards = verificar_hazards($conjuntos);
    $instrucaos = inserir_nops($conjuntos, $hazards, $forwarding);
    foreach ($instrucaos as $instrucao) {
        fwrite($outputFile, implode(" ", $instrucao) . "\n");
    }
    fwrite($outputFile2, $teste . "\n");

    echo implode(", ", $hazards);
    fclose($inputFile);
    fclose($outputFile);
    fclose($outputFile2);
} else {
    echo "Erro ao abrir o arquivo.\n";
}
