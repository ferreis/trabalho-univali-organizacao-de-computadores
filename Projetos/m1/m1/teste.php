<?php

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
$alu_instructions = ['ADD', 'SUB', 'SLL', 'SLT', 'SLTU', 'XOR', 'SRL', 'SRA', 'OR', 'AND'];
$jump_instructions = ['JAL', 'JALR'];
$branch_instructions = ['BEQ', 'BNE', 'BLT', 'BGE', 'BLTU', 'BGEU'];
$memory_instructions = ['LB', 'LH', 'LW', 'LBU', 'LHU', 'SB', 'SH', 'SW'];

// Função para classificar instruções
function classify_instruction($instruction, &$categories, $alu_instructions, $jump_instructions, $branch_instructions, $memory_instructions)
{
    if (in_array($instruction, $alu_instructions)) {
        $categories['alu']++;
    } elseif (in_array($instruction, $jump_instructions)) {
        $categories['jump']++;
    } elseif (in_array($instruction, $branch_instructions)) {
        $categories['branch']++;
    } elseif (in_array($instruction, $memory_instructions)) {
        $categories['memory']++;
    } else {
        $categories['other']++;
    }
    $categories['total']++;
}

// Ler o arquivo de memória de instrução
$file = fopen('teste.txt', 'r');
if ($file) {
    while (($line = fgets($file)) !== false) {
        // Extrair a instrução hexadecimal
        if (preg_match('/^\s*([0-9A-Fa-f]+)/', $line, $matches)) {
            $hex_instruction = $matches[1];
            // Converter a instrução hexadecimal para binário
            $bin_instruction = str_pad(base_convert($hex_instruction, 16, 2), 32, '0', STR_PAD_LEFT);
            // Extrair o opcode (7 bits menos significativos)
            $opcode = substr($bin_instruction, -7);
            // Mapear o opcode para a instrução correspondente
            $instruction_map = [
                '0110011' => 'ADD',
                '0010011' => 'ADDI',
                '1101111' => 'JAL',
                '1100111' => 'JALR',
                '1100011' => 'BEQ',
                '0000011' => 'LB',
                '0100011' => 'SB'
                // Adicionar mais mapeamentos conforme necessário
            ];
            $instruction = $instruction_map[$opcode] ?? 'UNKNOWN';
            // Classificar a instrução
            classify_instruction($instruction, $categories, $alu_instructions, $jump_instructions, $branch_instructions, $memory_instructions);
        }
    }
    fclose($file);
}

// Exibir as estatísticas das instruções
echo "Estatísticas das Instruções:\n";
foreach ($categories as $category => $count) {
    echo "$category: $count\n";
}

