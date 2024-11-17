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
    public $trava;


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
            'trava' => $this->trava
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
function verificarHazardInstrucao($instrucao1, $instrucao2, $forwardingImplementado)
{
    // A instrução tipo 'store' não escreve em 'rd', logo não causa hazards.
    if ($instrucao1['tipo'] == "store") {
        return false;
    }

    // Ignora NOPs manuais
    if ($instrucao1['nop'] || $instrucao2['nop']) {
        return false;
    }

    // Ignora 'ecalls'
    if ($instrucao2['instrucao'] == "00000000000000000000000001110011") {
        return false;
    }

    // Se 'forwarding' está implementado, só procura por hazards onde  a origem é 'load'
    if ($forwardingImplementado && $instrucao1['tipo'] != "load") {
        return false;
    }

    // Tipos de instrução que usam 'rs1'
    $tiposUsamRs1 = ["alu", "load", "store", "branch"];
    // Tipos de instrução que usam 'rs2'
    $tiposUsamRs2 = ["alu", "store", "branch"];

    // Verifica conflito com 'rs1'
    if (in_array($instrucao2['tipo'], $tiposUsamRs1) && $instrucao1['rd'] == $instrucao2['rs1'] && $instrucao1['rd'] != "00000") {
        return true;
    }

    // Verifica conflito com 'rs2'
    if (in_array($instrucao2['tipo'], $tiposUsamRs2) && $instrucao1['rd'] == $instrucao2['rs2'] && $instrucao1['rd'] != "00000") {
        return true;
    }

    return false;
}

// Função que verifica hazards em um conjunto de instruções
function verificarHazards($instrucoes, $forwarding)
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
            if (verificarHazardInstrucao($instrucoes[$x], $instrucoes[$y], $forwarding)) {
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

            if (verificarHazardInstrucao($instrucoes[$conflitos[$i]], $instrucoes[$j], $forwarding)) {
                for ($k = 0; $k < $qtd_nops; $k++) {
                    $no_operator->motivo_nop = "Inserido NOP devido a conflito entre rd={$instrucoes[$conflitos[$i]]['rd']}, rs1={$instrucoes[$j]['rs1']}, rs2={$instrucoes[$j]['rs2']}";
                    array_splice($instrucoes, $conflitos[$i] + 1, 0, [$no_operator->toArray()]);
                }
            }
            $qtd_nops--;
        }
    }

    return $instrucoes;
}
function inserirNopsEmDesvios($instrucoes)
{
    // Itera pelas instruções na ordem inversa
    for ($i = count($instrucoes) - 1; $i >= 0; $i--) {
        // Verifica se a instrução atual é um salto
        if ($instrucoes[$i]['tipo'] == "jump" || $instrucoes[$i]['tipo'] == "branch" && $instrucoes[$i+1]['nop'] == false) {
            // Cria um novo NOP
            $no_operator = new ConjuntoInstrucao();
            $no_operator->instrucao = "00000000000000000000000000110011"; // Representação da instrução NOP
            $no_operator->opcode = "0110011";
            $no_operator->rd = "00000";
            $no_operator->funct3 = "000";
            $no_operator->rs1 = "00000";
            $no_operator->rs2 = "00000";
            $no_operator->funct7 = "0000000";
            $no_operator->tipo = "NOP";
            $no_operator->motivo_nop = "Inserido NOP devido a Desvio do tipo {$instrucoes[$i]['tipo']}";
            $no_operator->nop = true;

            // Insere NOP antes da instrução de salto
            array_splice($instrucoes, $i+1, 0, [$no_operator->toArray()]);
            array_splice($instrucoes, $i+1, 0, [$no_operator->toArray()]);
        }
    }

    return $instrucoes;
}

function aplicarReordenacao(array $instrucoes, array $hazards, bool $forwardingImplementado): array {
    // Passa por todas as hazards
    for ($i = 0; $i < count($hazards); $i++) {
        $instrucaoEscolhida = null;
        $instrucaoEscolhidaDefinida = false;
        $indiceInstrucaoEscolhida = 0;

        // Passa por todas as instruções abaixo da linha da hazard
        for ($j = $i + 1; $j < count($instrucoes); $j++) {
            // Não considera instruções que já foram reordenadas (com trava ativa)
            if (isset($instrucoes[$j]['trava']) && $instrucoes[$j]['trava']) {
                continue;
            }

            // Validação da linha para entrar em hazard+1 //

            // Passa linha $j se ela conflita com a linha da hazard
            if (verificarHazardInstrucao($instrucoes[$hazards[$i]], $instrucoes[$j], $forwardingImplementado)) {
                continue;
            }

            // Passa linha $j se não tiver forwarding e ela conflitar com hazard-1
            if (!$forwardingImplementado && $i > 0) {
                if (verificarHazardInstrucao($instrucoes[$hazards[$i] - 1], $instrucoes[$j], $forwardingImplementado)) {
                    continue;
                }
            }

            $linhaValidaDepois = true;
            // Passa linha $j se as linhas após a hazard não terão conflito com a linha $j
            for ($k = $hazards[$i] + 1; $k <= $hazards[$i] + ($forwardingImplementado ? 1 : 2); $k++) {
                // Evita $k de atravessar o tamanho máximo do vetor
                if ($k > count($instrucoes) - 1) continue;
                if (verificarHazardInstrucao($instrucoes[$k], $instrucoes[$j], $forwardingImplementado)) {
                    $linhaValidaDepois = false;
                    break;
                }
            }

            // Validação da linha ao sair de seu ponto de origem //

            $linhaValidaAntes = true;
            // Passa linha $j se a linha $j-1 não tiver conflito com as próximas linhas caso a linha $j seja removida
            for ($k = $j + 1; $k <= $j + ($forwardingImplementado ? 1 : 2); $k++) {
                // Evita $k de atravessar o tamanho máximo do vetor
                if ($k > count($instrucoes) - 1) continue;
                if (verificarHazardInstrucao($instrucoes[$j - 1], $instrucoes[$k], $forwardingImplementado)) {
                    $linhaValidaAntes = false;
                    break;
                }
            }

            if ($linhaValidaAntes && $linhaValidaDepois) {
                $instrucaoEscolhida = $instrucoes[$j+1];
                $instrucaoEscolhidaDefinida = true;
                $indiceInstrucaoEscolhida = $j;
                break;
            }
        }

        // Há uma instrução que pode ser reordenada
        if ($instrucaoEscolhidaDefinida) {
            // Marca a instrução como reordenada e adiciona o motivo
            $instrucaoEscolhida['trava'] = true;
            $instrucaoEscolhida['motivo_nop'] = "Reordenado para evitar hazard";
            
            // Insere a instrução após a hazard
            array_splice($instrucoes, $hazards[$i] + 1, 0, [$instrucaoEscolhida]);
        
            // Remove a instrução de sua posição original
            unset($instrucoes[$indiceInstrucaoEscolhida + 1]);
            
            // Reindexa o array
            $instrucoes = array_values($instrucoes);
        }
    }

    return $instrucoes;
}
function delayBranch(array $instrucoes): array
{
    for ($i = 0; $i < count($instrucoes); $i++) {
        // Verifica se a instrução atual é um branch ou jump
        if ($instrucoes[$i]['tipo'] === "branch") {
            $espacos_atraso = 2; // Considerando 2 slots de atraso
            $instrucoes_movidas = [];

            // Tenta preencher os espaços de atraso com instruções úteis
            for ($j = $i - 1; $j >= 0 && count($instrucoes_movidas) < $espacos_atraso; $j--) {
                $instrucao = $instrucoes[$j];

                // Ignora NOPs e instruções já travadas ou reordenadas
                if ($instrucao['nop'] || (isset($instrucao['trava']) && $instrucao['trava'])) {
                    continue;
                }

                // Verifica se a instrução pode ser movida sem causar hazards
                $podeMover = true;
                for ($k = $i + 1; $k <= $i + $espacos_atraso && $k < count($instrucoes); $k++) {
                    if (verificarHazardInstrucao($instrucao, $instrucoes[$k], true)) {
                        $podeMover = false;
                        break;
                    }
                            // Verifica se encontrou uma instrução que deve parar a busca
                        if ($instrucoes[$k]['tipo'] != "jump" || 
                        $instrucoes[$k]['tipo'] != "branch" || 
                        (isset($instrucoes[$k]['trava']) && $instrucoes[$k]['trava'])) {
                        $podeMover = false;
                        break; // Para a busca imediatamente
                    }
                }

                if ($podeMover) {
                    // Marca a instrução como movida
                    $instrucao['trava'] = true;
                    $instrucao['motivo_nop'] = "Movida para preencher delay slot do tipo {$instrucoes[$i]['tipo']}";

                    // Adiciona a instrução movida aos slots
                    $instrucoes_movidas[] = $instrucao;
                    unset($instrucoes[$j]);
                }
            }

            // Reindexa o array após mover instruções
            $instrucoes = array_values($instrucoes);

            // Adiciona as instruções movidas após o branch/jump
            foreach (array_reverse($instrucoes_movidas) as $movida) {
                array_splice($instrucoes, $i + 1, 0, [$movida]);
                $i++; // Ajusta o índice para considerar a nova posição
            }
        }
    }

    return $instrucoes;
}

function reordenarJump($instrucoes) {
    // Itera pelas instruções da última para a primeira
    for ($i = count($instrucoes) - 1; $i >= 0; $i--) {
        // Verifica se a instrução atual é um jump
        if ($instrucoes[$i]['tipo'] == "jump") {
            $posicoes_nop = [];
            $qtd_nops_necessarios = 2;
            
            // Encontra os NOPs após o jump
            for ($j = $i + 1; $j < count($instrucoes) && count($posicoes_nop) < $qtd_nops_necessarios; $j++) {
                if ($instrucoes[$j]['nop']) {
                    $posicoes_nop[] = $j;
                }
            }

            // Se não encontrou 2 NOPs, continua para próxima instrução
            if (count($posicoes_nop) < $qtd_nops_necessarios) {
                continue;
            }

            // Procura instruções acima do jump que podem ser movidas
            $instrucoes_movidas = 0;
            $posicoes_originais = [];
            
            // Variável para controlar se encontramos uma instrução que deve parar a busca
            $deve_parar = false;
            
            for ($k = $i - 1; $k >= 0 && $instrucoes_movidas < $qtd_nops_necessarios; $k--) {
                // Verifica se encontrou uma instrução que deve parar a busca
                if ($instrucoes[$k]['tipo'] == "jump" ||
                    $instrucoes[$k]['tipo'] == "branch" ||
                    (isset($instrucoes[$k]['trava']) && $instrucoes[$k]['trava'])) {
                    $deve_parar = true;
                    break; // Para a busca imediatamente
                }

                // Verifica se a instrução pode ser movida
                if (!$instrucoes[$k]['nop']) {
                    $pode_mover = true;
                    // Verifica dependências com instruções intermediárias
                    for ($m = $k + 1; $m <= $i; $m++) {
                        if (verificarHazardInstrucao($instrucoes[$k], $instrucoes[$m], true)) {
                            $pode_mover = false;
                            break;
                        }
                    }

                    if ($pode_mover) {
                        // Move a instrução para a posição do NOP
                        $instrucao_mover = $instrucoes[$k];
                        $instrucao_mover['motivo_nop'] = "Reordenado para delay slot de " . $instrucoes[$i]['tipo'];
                        $instrucao_mover['trava'] = true;
                        // Guarda a posição original para remoção posterior
                        $posicoes_originais[] = $k;
                        // Substitui o NOP pela instrução movida
                        $instrucoes[$posicoes_nop[$instrucoes_movidas]] = $instrucao_mover;
                        $instrucoes_movidas++;
                    }
                }
            }

            // Se encontrou uma instrução que deve parar a busca e ainda não moveu todas as instruções necessárias
            // mantém os NOPs originais
            if ($deve_parar && $instrucoes_movidas < $qtd_nops_necessarios) {
                continue; // Pula para o próximo jump
            }
        }
    }
    return $instrucoes;
}

function salvarTxt($instrucoes, $fileResource) {
    // Define o cabeçalho com tamanhos fixos e centralizado
    $cabecalho = str_pad("Linha", 5, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("Instrução", 37, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("opcode", 7, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("rd", 5, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("funct3", 6, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("rs1", 5, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("rs2", 5, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("funct7", 7, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("Tipo", 10, ' ', STR_PAD_BOTH) . "| " . 
                 str_pad("trava", 10, ' ', STR_PAD_BOTH). "| ".
                 str_pad("Nop", 4, ' ', STR_PAD_BOTH) . "| Motivo";
    fwrite($fileResource, $cabecalho . "\n");

    // Linha de separação
    $linhaSeparadora = str_pad("", 5, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 35, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 7, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 5, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 6, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 5, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 5, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 7, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 10, '_', STR_PAD_BOTH) . "| " . 
                       str_pad("", 4, '_', STR_PAD_BOTH) . "|".
                       str_pad("", 80, '_');
    fwrite($fileResource, $linhaSeparadora . "\n");

    foreach ($instrucoes as $index => $conjunto) {
        $numeroLinha = str_pad(($index + 1), 3, '0', STR_PAD_LEFT);
        $linha = str_pad($numeroLinha, 5) . "| " . 
                 str_pad($conjunto['instrucao'], 35) . "| " . 
                 str_pad($conjunto['opcode'], 7) . "| " . 
                 str_pad($conjunto['rd'], 5) . "| " . 
                 str_pad($conjunto['funct3'], 6) . "| " . 
                 str_pad($conjunto['rs1'], 5) . "| " . 
                 str_pad($conjunto['rs2'], 5) . "| " . 
                 str_pad($conjunto['funct7'], 7) . "| " . 
                 str_pad($conjunto['tipo'], 10) . "| " . 
                 str_pad(($conjunto['nop'] ? 'Sim' : 'Não '), 4) ."| " ;

        // Adiciona motivo NOP se houver
        if (!empty($conjunto['motivo_nop'])) {
            $linha .=  $conjunto['motivo_nop'];
        } 

        fwrite($fileResource, $linha . "\n");
    }
}


function lerArquivo($inputFile)
{
    if (!file_exists($inputFile)) {
        die("Arquivo não encontrado!");
    }

    $arquivo = fopen($inputFile, "r");
    $instrucoes = [];

    while (($instrucao = fgets($arquivo)) !== false) {
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
        $instrucoes[] = $conjunto->toArray();
    }

    fclose($arquivo);
    return $instrucoes; // Retorna a lista de instruções
}

// Função para processar as instruções com ou sem forwarding
function processarInstrucoes($inputPath, $outputFinal, $tecnica, $forwarding)
{
    $outputFile = fopen($outputFinal, "w"); // Arquivo de saída para instruções com NOPs
    $hazards = [];

    if ($outputFile) {

        $instrucoes = lerArquivo($inputPath);
        switch ($tecnica) {
            case 1:
                $hazards = verificarHazards($instrucoes, $forwarding);
                $instrucoes = aplicarReordenacao($instrucoes, $hazards, $forwarding);
                $hazards = verificarHazards($instrucoes, $forwarding);
                $instrucoes = inserir_nops($instrucoes, $hazards, $forwarding);
                break;
            case 2:
                $instrucoes = inserirNopsEmDesvios($instrucoes);
                break;
            case 3:
                $instrucoes = inserirNopsEmDesvios($instrucoes);
                $instrucoes =  reordenarJump($instrucoes);
                $instrucoes = delayBranch($instrucoes);
                break;
            case 4:
                $hazards = verificarHazards($instrucoes, $forwarding);
                $instrucoes = aplicarReordenacao($instrucoes, $hazards, $forwarding);
                $hazards = verificarHazards($instrucoes, $forwarding);
                $instrucoes = inserir_nops($instrucoes, $hazards, $forwarding);
                $instrucoes = inserirNopsEmDesvios($instrucoes);
                $instrucoes =  reordenarJump($instrucoes);
                $instrucoes = delayBranch($instrucoes);
                break;
            default:
                break;
        }
        salvarTxt($instrucoes, $outputFile);

        
        // Fecha os arquivos
        fclose($outputFile);
    } else {
        echo "Erro ao abrir os arquivos.";
    }
}

// Executa o processamento com e sem forwarding, incluindo arquivo reordenado
processarInstrucoes("lerHex.txt", "0_saida_original.txt", 0 , true);
processarInstrucoes("lerHex.txt", "1_forwarding_reordenamento.txt", 1 , true);
processarInstrucoes("lerHex.txt", "2_nops_conflitos_controle.txt", 2 , true);
processarInstrucoes("lerHex.txt", "3_desvio_retardado.txt", 3 , true);
processarInstrucoes("lerHex.txt", "4_forwarding_desvio_retardado.txt", 4 , true);
