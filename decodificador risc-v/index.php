<?php


// Classe que representa uma instrução de um conjunto de instruções
class ConjuntoInstrucao
{
    // Propriedades da instrução
    public $instrucao; // A instrução em formato binário
    public $opcode;    // Código da operação
    public $rd;        // Registrador de destino
    public $funct3;    // Função 3
    public $rs1;       // Registrador fonte 1
    public $rs2;       // Registrador fonte 2
    public $funct7;    // Função 7
    public $tipo;      // Tipo da instrução
    public $nop;       // Indica se é uma instrução NOP (No Operation)
    public $motivo_nop;


    // Método que converte os atributos da instrução em um array
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
            'motivo_nop' => $this->motivo_nop,
        ];
    }
}

// Inicialização das categorias de instruções RISC-V
$categories = [
    'total' => 0,
    'alu' => 0,
    'jump' => 0,
    'branch' => 0,
    'memory' => 0,
    'other' => 0
];

// Definição das instruções RISC-V e suas respectivas categorias
$alu_instructions = ['0110011', '0010011']; // Instruções ALU
$jump_instructions = ['1101111', '1100111']; // Instruções de salto
$branch_instructions = ['1100011']; // Instruções de branch
$memory_instructions = ['0001111', '1110011']; // Instruções de memória
$load_instructions = ['0000011']; // Instruções de carga
$store_instructions = ['0100011']; // Instruções de armazenamento

// Função para determinar o tipo de instrução com base no opcode
function opcode($opcode, $verificarTipo)
{
    global $categories, $alu_instructions, $jump_instructions, $branch_instructions, $memory_instructions, $load_instructions, $store_instructions;

    // Verifica se o opcode pertence a uma categoria específica e atualiza as contagens
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

// Função que converte um caractere hexadecimal em uma string binária
function obterBinario($c)
{
    // Mapeamento de caracteres hexadecimais para binários
    $map = [
        '0' => "0000", '1' => "0001", '2' => "0010", '3' => "0011",
        '4' => "0100", '5' => "0101", '6' => "0110", '7' => "0111",
        '8' => "1000", '9' => "1001", 'A' => "1010", 'a' => "1010",
        'B' => "1011", 'b' => "1011", 'C' => "1100", 'c' => "1100",
        'D' => "1101", 'd' => "1101", 'E' => "1110", 'e' => "1110",
        'F' => "1111", 'f' => "1111"
    ];
    // Retorna o valor binário ou "????" se o caractere não for reconhecido
    return $map[$c] ?? "????";
}

// Função para verificar se há hazard entre duas instruções
function verificar_hazard_instrucao($instrucao_1, $instrucao_2, $forwardingImplementado)
{
    // A instrução tipo 'store' não escreve em 'rd', logo não causa hazards.
    if ($instrucao_1['tipo'] == "store") return false;

    // Ignora NOPs manuais
    if ($instrucao_1['nop']) return false;
    if ($instrucao_2['nop']) return false;

    // Ignora 'ecalls'
    if ($instrucao_2['instrucao'] == "00000000000000000000000001110011") return false;

    // Se 'forwarding' está implementado, só procura por hazards onde a origem é 'load'
    if ($forwardingImplementado && $instrucao_1['tipo'] != "load") return false;

    // Tipos de instrução que usam 'rs1'
    $tiposUsamRs1 = ["alu", "load", "store", "branch"];
    // Tipos de instrução que usam 'rs2'
    $tiposUsamRs2 = ["alu", "store", "branch"];

    // Verifica conflito com 'rs1'
    if (in_array($instrucao_2['tipo'], $tiposUsamRs1)) {
        if ($instrucao_1['rd'] == $instrucao_2['rs1'] && $instrucao_1['rd'] != "00000") {
            return ['conflict' => true, 'rs' => 'rs1'];
        }
    }

    // Verifica conflito com 'rs2'
    if (in_array($instrucao_2['tipo'], $tiposUsamRs2)) {
        if ($instrucao_1['rd'] == $instrucao_2['rs2'] && $instrucao_1['rd'] != "00000") {
            return ['conflict' => true, 'rs' => 'rs2'];
        }
    }

    return false;
}

// Função que verifica hazards em um conjunto de instruções
function verificar_hazards($instrucoes, $forwarding)
{
    $hazards = []; // Array para armazenar índices de hazards
    for ($x = 0; $x < count($instrucoes); $x++) {
        for ($y = $x + 1; $y < $x + 3; $y++) { // Verifica as próximas duas instruções
            if ($y >= count($instrucoes)) {
                continue; // Ignora se o índice estiver fora do limite
            }
            if ($instrucoes[$x]['rd'] == "00000") {
                continue; // Ignora se não houver registrador de destino
            }
            // Ignora hazard se for uma instrução de load com rs1 igual a rd
            if ($instrucoes[$x]['tipo'] == "load" && $instrucoes[$x]['rs1'] == "rd") {
                continue;
            }
            // Se há hazard, armazena o índice
            if (verificar_hazard_instrucao($instrucoes[$x], $instrucoes[$y], $forwarding)) {
                $hazards[] = $x;
                echo "Conflito entre instruções: rd={$instrucoes[$x]['rd']}, rs1={$instrucoes[$y]['rs1']}, rs2={$instrucoes[$y]['rs2']}\n";
            }
        }
    }
    return $hazards; // Retorna os índices dos hazards encontrados
}

// Função para inserir instruções NOP (No Operation) em um conjunto de instruções
function inserir_nops($instrucoes, $conflitos, $forwarding)
{
    // Cria um NOP
    $no_operator = new ConjuntoInstrucao();
    $no_operator->instrucao = "00000000000000000000000000110011"; // Instrução NOP (add x0, x0, x0)
    $no_operator->opcode = "0110011";
    $no_operator->rd = "00000";
    $no_operator->funct3 = "000";
    $no_operator->rs1 = "00000";
    $no_operator->rs2 = "00000";
    $no_operator->funct7 = "0000000";
    $no_operator->tipo = "NOP";
    $no_operator->nop = true;
    $no_operator->motivo_nop = "";

    // Insere NOPs para cada conflito identificado
    for ($i = count($conflitos) - 1; $i >= 0; $i--) {
        // Insere 2 NOPs se não houver forwarding, ou 1 se houver
        $qtd_nops = $forwarding ? 1 : 2;

        // Prepara o NOP com o motivo
        for ($j = $conflitos[$i]+1; $j <= $conflitos[$i] + ($forwarding ? 1 : 2); $j++) {
            if ($j > count($instrucoes) - 1){
                continue;
            }

            if (verificar_hazard_instrucao($instrucoes[$conflitos[$i]], $instrucoes[$j], $forwarding)) {
                for ($k = 0; $k < $qtd_nops; $k++)
                $no_operator->motivo_nop = "Inserido NOP devido a conflito entre rd={$instrucoes[$conflitos[$i]]['rd']}, rs1={$instrucoes[$j]['rs1']}, rs2={$instrucoes[$j]['rs2']}";
                array_splice($instrucoes, $conflitos[$i] + 1, 0, [$no_operator->toArray()]);
            }
            $qtd_nops--;
        }
    }

    return inserir_nops_em_jump($instrucoes);
}
function inserir_nops_em_jump($instrucoes)
{
    $no_operator = new ConjuntoInstrucao();
    $no_operator->instrucao = "00000000000000000000000000110011"; // Representação da instrução NOP
    $no_operator->opcode = "0110011";
    $no_operator->rd = "00000";
    $no_operator->funct3 = "000";
    $no_operator->rs1 = "00000";
    $no_operator->rs2 = "00000";
    $no_operator->funct7 = "0000000";
    $no_operator->tipo = "NOP";

    // Itera pelas instruções na ordem inversa
    for ($i = count($instrucoes) - 1; $i >= 0; $i--) {
        // Verifica se a instrução atual é um salto
        if ($instrucoes[$i]['tipo'] == "jump") {
            // Insere NOP antes da instrução de salto
            array_splice($instrucoes, $i, 0, [$no_operator->toArray()]);
        }
    }

    return $instrucoes;
}
// Função para processar as instruções com ou sem forwarding
function processar_instrucoes($inputPath, $outputOriginal, $outputFinal, $forwarding)
{
    $inputFile = fopen($inputPath, "r"); // Abre o arquivo de entrada
    $outputFile = fopen($outputFinal, "w"); // Abre o arquivo de saída para instruções processadas
    $outputFileOriginal = fopen($outputOriginal, "w"); // Arquivo para gravação das instruções originais

    if ($inputFile && $outputFile && $outputFileOriginal) {
        $conjuntos = []; // Array de instruções

        // Lê e processa o arquivo de entrada
        while (($instrucao = fgets($inputFile)) !== false) {
            $hexString = trim($instrucao);
            $binaryString = '';

            // Converte cada caractere hexadecimal em binário
            foreach (str_split($hexString) as $c) {
                $binaryString .= obterBinario($c);
            }

            // Cria um novo objeto ConjuntoInstrucao
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
            $conjunto->motivo_nop = "";

            // Verifica se há NOPs para a instrução
            opcode($conjunto->opcode, false);
            $conjuntos[] = $conjunto->toArray();
        }

        // Detecta hazards e insere NOPs
        $hazards = verificar_hazards($conjuntos, $forwarding);
        $instrucaos_com_nops = inserir_nops($conjuntos, $hazards, $forwarding);

        // Grava as instruções originais e processadas
        foreach ($conjuntos as $instrucao) {
            fwrite($outputFileOriginal, implode(" ", $instrucao) . "\n");
        }

        foreach ($instrucaos_com_nops as $index => $conjunto) {
            $linha = ($index + 1) . ": " . $conjunto['instrucao'] . ' ' . $conjunto['opcode'] .
                ' ' . $conjunto['rd'] . ' ' . $conjunto['funct3'] . ' ' . $conjunto['rs1'] . ' ' .
                $conjunto['rs2'] . ' ' . $conjunto['funct7'] . ' ' . $conjunto['tipo'];

            // Adiciona motivo NOP se houver
            if ($conjunto['motivo_nop']) {
                $linha .= ' - ' . $conjunto['motivo_nop'];
            }

            fwrite($outputFile, $linha . "\n"); // Corrigido para escrever no arquivo de saída correto
        }

        // Fecha os arquivos
        fclose($inputFile);
        fclose($outputFile);
        fclose($outputFileOriginal);
    } else {
        echo "Erro ao abrir os arquivos.";
    }
}

// Executa o processamento com e sem forwarding
processar_instrucoes("lerHex.txt", "saida_original.txt", "saida_sem_forwarding.txt", false);
processar_instrucoes("lerHex.txt", "saida_original.txt", "saida_com_forwarding.txt", true);
