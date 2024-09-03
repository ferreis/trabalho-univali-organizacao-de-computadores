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
$alu_instructions = ['0110011', '0010011'];
$jump_instructions = ['1101111', '1100111'];
$branch_instructions = ['1100011'];
$memory_instructions = ['0001111', '1110011'];

function opcode($c)
{
    global $categories, $alu_instructions, $jump_instructions, $branch_instructions, $memory_instructions;

    if (in_array($c, $alu_instructions)) {
        $categories['alu']++;
    } elseif (in_array($c, $jump_instructions)) {
        $categories['jump']++;
    } elseif (in_array($c, $branch_instructions)) {
        $categories['branch']++;
    } elseif (in_array($c, $memory_instructions)) {
        $categories['memory']++;
    } else {
        $categories['other']++;
    }
    $categories['total']++;
}

// Função para obter o valor binário de um caractere hexadecimal
function obterBinario($c)
{
    switch ($c) {
        case '0':
            return "0000";
        case '1':
            return "0001";
        case '2':
            return "0010";
        case '3':
            return "0011";
        case '4':
            return "0100";
        case '5':
            return "0101";
        case '6':
            return "0110";
        case '7':
            return "0111";
        case '8':
            return "1000";
        case '9':
            return "1001";
        case 'A':
        case 'a':
            return "1010";
        case 'B':
        case 'b':
            return "1011";
        case 'C':
        case 'c':
            return "1100";
        case 'D':
        case 'd':
            return "1101";
        case 'E':
        case 'e':
            return "1110";
        case 'F':
        case 'f':
            return "1111";
        default:
            return "????";
    }
}

$inputFile = fopen("teste.txt", "r");

if ($inputFile) {
    while (($line = fgets($inputFile)) !== false) {
        $hexString = trim($line);
        $binaryString = "";
        $mostrarBinaryString = "";

        foreach (str_split($hexString) as $c) {
            $bin = obterBinario($c);
            $binaryString .= $bin;
            $mostrarBinaryString .= $bin . "";
        }

        // Extrair os últimos 7 dígitos binários
        $opcode_instructions = substr($binaryString, -7);
        $funct3 = substr($binaryString, -15, 3);
        opcode($opcode_instructions);
        echo "------------------------------------\n";
        echo "Hexadecimal: " . $hexString . "\n";
        echo "Binário: " . $mostrarBinaryString . "\n";
        echo "Funct3: " . $funct3 . "\n";


    }
    // Exibir as categorias
    foreach ($categories as $key => $value) {
        echo ucfirst($key) . ": " . $value . "\n";
    }
    echo "------------------------------------\n";
    fclose($inputFile);
} else {
    echo "Erro ao abrir o arquivo.\n";
}
?>
