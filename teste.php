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
function verificar_hazard_instrucao($instrucao_1, $instrucao_2)
{
    // Verifica se o registrador de destino da instrução 1 é um dos registradores fonte da instrução 2
    return $instrucao_1['rd'] == $instrucao_2['rs1']
        || $instrucao_1['rd'] == $instrucao_2['rs2'];
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
            if (verificar_hazard_instrucao($instrucoes[$x], $instrucoes[$y])) {
                $hazards[] = $x;
            }
        }
    }
    return $hazards; // Retorna os índices dos hazards encontrados
}

// Função para inserir instruções NOP (No Operation) em um conjunto de instruções
function inserir_nops($instrucoes, $hazards, $forwarding)
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

    // Percorre os hazards em ordem inversa
    for ($x = count($hazards) - 1; $x >= 0; $x--) {
        $qtd_nops = !$forwarding ? 2 : 1; // Determina a quantidade de NOPs a serem inseridos
        $hazardIndex = $hazards[$x];

        // Verifica se a instrução atual é um load
        if ($instrucoes[$hazardIndex]['tipo'] == 'load') {
            // Se a próxima instrução usar o registrador de destino do load
            for ($y = $hazardIndex + 1; $y < count($instrucoes); $y++) {
                if ( verificar_hazard_instrucao( $instrucoes[$hazardIndex], $instrucoes[$y])) {
                    // Insere NOPs
                    for ($k = 0; $k < $qtd_nops; $k++) {
                        array_splice($instrucoes, $hazardIndex, 0, [$no_operator->toArray()]);
                    }
                    break; // Sai do loop após inserir os NOPs
                }
            }
        } else {
            // Verifica hazards para outras instruções
            for ($y = $hazardIndex + 1; $y <= $hazardIndex + $qtd_nops; $y++) {
                if ($y >= count($instrucoes)) {
                    continue; // Ignora se o índice estiver fora do limite
                }
                // Verifica se há hazard entre a instrução original e a próxima
                if (verificar_hazard_instrucao($instrucoes[$hazardIndex], $instrucoes[$y])) {
                    // Insere NOPs na posição correta
                    for ($k = 0; $k < $qtd_nops; $k++) {
                        array_splice($instrucoes, $hazardIndex + 1, 0, [$no_operator->toArray()]);
                    }
                }
            }
        }
    }
    return inserir_nops_em_jump($instrucoes); // Retorna o conjunto de instruções com NOPs inseridos
}

// Função para inserir NOPs antes de instruções de salto
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
    $no_operator->tipo = "NOP motivo: Jump";

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

// Preparação de arquivos para leitura e escrita
$inputFile = fopen("lerHex.txt", "r"); // Abre o arquivo para leitura
$outputFile = fopen("gravar.txt", "w"); // Abre o arquivo para escrita
$outputFile2 = fopen("convertidoBinario.txt", "w"); // Abre o arquivo para escrita do binário convertido

if ($inputFile && $outputFile) {
    $conjuntos = []; // Array para armazenar as instruções
    $teste = ''; // String para teste
    while (($instrucao = fgets($inputFile)) !== false) {
        $hexString = trim($instrucao); // Lê e remove espaços em branco
        $binaryString = ""; // Inicializa string binária
        foreach (str_split($hexString) as $c) {
            $binaryString .= obterBinario($c); // Converte cada caractere hexadecimal em binário
        }
        $teste .= $binaryString . "\n"; // Adiciona a string binária ao teste
        $conjunto = new ConjuntoInstrucao(); // Cria uma nova instância de ConjuntoInstrucao
        $conjunto->instrucao = $binaryString; // Armazena a string binária
        $conjunto->opcode = substr($binaryString, 25, 7); // Extrai o opcode
        $conjunto->rd = substr($binaryString, 20, 5); // Extrai o registrador de destino
        $conjunto->funct3 = substr($binaryString, 17, 3); // Extrai a função 3
        $conjunto->rs1 = substr($binaryString, 12, 5); // Extrai o registrador fonte 1
        $conjunto->rs2 = substr($binaryString, 7, 5); // Extrai o registrador fonte 2
        $conjunto->funct7 = substr($binaryString, 0, 7); // Extrai a função 7
        $conjunto->tipo = opcode($conjunto->opcode, true); // Determina o tipo da instrução
        $conjunto->nop = false; // Inicializa como não NOP
        opcode($conjunto->opcode, false); // Atualiza as contagens de categorias
        $conjuntos[] = $conjunto->toArray(); // Adiciona a instrução ao conjunto
    }
    $forwarding = false; // Define o forwarding como falso
    $hazards = verificar_hazards($conjuntos, $forwarding); // Verifica hazards nas instruções
    $instrucaos = inserir_nops($conjuntos, $hazards, $forwarding); // Insere NOPs nas instruções
    // Escreve as instruções finais nos arquivos de saída
    foreach ($instrucaos as $instrucao) {
        fwrite($outputFile, implode(" ", $instrucao) . "\n");
    }
    fwrite($outputFile2, $teste . "\n"); // Grava as instruções convertidas em binário

    // Exibe os índices dos hazards encontrados
    echo implode(", ", $hazards);
    fclose($inputFile); // Fecha o arquivo de entrada
    fclose($outputFile); // Fecha o arquivo de saída
    fclose($outputFile2); // Fecha o arquivo de saída
} else {
    echo "Erro ao abrir o arquivo.\n";
}
