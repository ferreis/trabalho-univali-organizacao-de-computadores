Aqui estão as tabelas com as instruções RISC-V para três casos: sem NOP, com forwarding e com forwarding + reordenação.

### Sem Forwarding

| Linha | Instrução                               | opcode  |  rd  | funct3 |  rs1 |  rs2 | funct7 |    Tipo   | Nop | Risc-v                   |
|-------|-----------------------------------------|---------|------|--------|------|------|--------|-----------|-----|--------------------------|
| 001   | 00000010100000000000000001101111       | 1101111 | 00000| 000    | 00000| 01000| 0000001| jump      | Não | jal zero main            |
| 002   | 00000000011100110111001010110011       | 0110011 | 00101| 111    | 00110| 00111| 0000000| alu       | Não | and t0, t1, t2           |
| 003   | 01000001111011101000111000110011       | 0110011 | 11100| 000    | 11101| 11110| 0100000| alu       | Não | sub t3, t4, t5           |
| 004   | 00000000000000101010010000000011       | 0000011 | 01000| 010    | 00101| 00000| 0000000| load      | Não | lw  s0, 0(t0)            |
| 005   | 00000000100001000000001010110011       | 0110011 | 00101| 000    | 01000| 01000| 0000000| alu       | Não | add t0, s0, s0           |
| 006   | 11111110010111100000100011100011       | 1100011 | 10001| 000    | 11100| 00101| 1111111| branch    | Não | beq t3, t0, inicio       |
| 007   | 01000000010111110000111010110011       | 0110011 | 11101| 000    | 11110| 00101| 0100000| alu       | Não | sub t4, t5, t0           |
| 008   | 00000000000010010010010010000011       | 0000011 | 01001| 010    | 10010| 00000| 0000000| load      | Não | lw  s1, 0(s2)            |
| 009   | 00000110010010100000100110010011       | 0010011 | 10011| 000    | 10100| 00100| 0000011| alu       | Não | addi s3, s4, 100         |
| 010   | 00000000000000001000000011100111       | 1100111 | 00001| 000    | 00001| 00000| 0000000| jump      | Não | jalr ra                  |
| 011   | 11111101110111111111000011101111       | 1101111 | 00001| 111    | 11111| 11101| 1111110| jump      | Não | jal ra, inicio           |

### Com Forwarding

| Linha | Instrução                               | opcode  |  rd  | funct3 |  rs1 |  rs2 | funct7 |    Tipo   | Nop | Risc-v                   |
|-------|-----------------------------------------|---------|------|--------|------|------|--------|-----------|-----|--------------------------|
| 001   | 00000010100000000000000001101111       | 1101111 | 00000| 000    | 00000| 01000| 0000001| jump      | Não | jal zero main            |
| 002   | 00000000011100110111001010110011       | 0110011 | 00101| 111    | 00110| 00111| 0000000| alu       | Não | and t0, t1, t2           |
| 003   | 01000001111011101000111000110011       | 0110011 | 11100| 000    | 11101| 11110| 0100000| alu       | Não | sub t3, t4, t5           |
| 004   | 00000000000000101010010000000011       | 0000011 | 01000| 010    | 00101| 00000| 0000000| load      | Não | lw  s0, 0(t0)            |
| -> 005| 00000000000000000000000000110011       | 0110011 | 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP <-          |
| 006   | 00000000100001000000001010110011       | 0110011 | 00101| 000    | 01000| 01000| 0000000| alu       | Não | add t0, s0, s0           |
| 007   | 11111110010111100000100011100011       | 1100011 | 10001| 000    | 11100| 00101| 1111111| branch    | Não | beq t3, t0, inicio       |
| 008   | 01000000010111110000111010110011       | 0110011 | 11101| 000    | 11110| 00101| 0100000| alu       | Não | sub t4, t5, t0           |
| 009   | 00000000000010010010010010000011       | 0000011 | 01001| 010    | 10010| 00000| 0000000| load      | Não | lw  s1, 0(s2)            |
| 010   | 00000110010010100000100110010011       | 0010011 | 10011| 000    | 10100| 00100| 0000011| alu       | Não | addi s3, s4, 100         |
| 011   | 00000000000000001000000011100111       | 1100111 | 00001| 000    | 00001| 00000| 0000000| jump      | Não | jalr ra                  |
| 012   | 11111101110111111111000011101111       | 1101111 | 00001| 111    | 11111| 11101| 1111110| jump      | Não | jal ra, inicio           |


### Com Forwarding + Reordenação

| Linha | Instrução                               | opcode  |  rd  | funct3 |  rs1 |  rs2 | funct7 |    Tipo   | Nop | Risc-v                   |
|-------|-----------------------------------------|---------|------|--------|------|------|--------|-----------|-----|--------------------------|
| 001   | 00000010100000000000000001101111       | 1101111 | 00000| 000    | 00000| 01000| 0000001| jump      | Não | jal zero main            |
| 002   | 00000000011100110111001010110011       | 0110011 | 00101| 111    | 00110| 00111| 0000000| alu       | Não | and t0, t1, t2           |
| -> 003| 01000001111011101000111000110011       | 0110011 | 11100| 000    | 11101| 11110| 0100000| alu       | Não | sub t3, t4, t5 <- Sai daqui |
| 004   | 00000000000000101010010000000011       | 0000011 | 01000| 010    | 00101| 00000| 0000000| load      | Não | lw  s0, 0(t0)            |
| -> 005| 01000001111011101000111000110011       | 0110011 | 11100| 000    | 11101| 11110| 0100000| alu       | Não | NOP <- Vem para cá       |
| 006   | 00000000100001000000001010110011       | 0110011 | 00101| 000    | 01000| 01000| 0000000| alu       | Não | add t0, s0, s0           |
| 007   | 11111110010111100000100011100011       | 1100011 | 10001| 000    | 11100| 00101| 1111111| branch    | Não | beq t3, t0, inicio       |
| 008  

 | 01000000010111110000111010110011       | 0110011 | 11101| 000    | 11110| 00101| 0100000| alu       | Não | sub t4, t5, t0           |
| 009   | 00000000000010010010010010000011       | 0000011 | 01001| 010    | 10010| 00000| 0000000| load      | Não | lw  s1, 0(s2)            |
| 010   | 00000110010010100000100110010011       | 0010011 | 10011| 000    | 10100| 00100| 0000011| alu       | Não | addi s3, s4, 100         |
| 011   | 00000000000000001000000011100111       | 1100111 | 00001| 000    | 00001| 00000| 0000000| jump      | Não | jalr ra                  |
| 012   | 11111101110111111111000011101111       | 1101111 | 00001| 111    | 11111| 11101| 1111110| jump      | Não | jal ra, inicio           |



Se precisar de mais ajustes ou explicações sobre o código, é só avisar!

# Solução 2: Identificação de Fluxo de Instruções

Utilizar cores para identificar o fluxo de instruções, destacando quais instruções saem e para onde serão inseridas.

| Linha |               Instrução               | opcode |  rd  | funct3 |  rs1 |  rs2 | funct7 |    Tipo   | NOP | RISC-V                      |
|-------|---------------------------------------|--------|------|--------|------|------|--------|-----------|-----|------------------------------|
| 001   | 00000010100000000000000001101111     | 1101111| 00000| 000    | 00000| 01000| 0000001| jump      | Não | jal zero main                |
| -> 002| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| -> 003| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| 004   | 00000000011100110111001010110011     | 0110011| 00101| 111    | 00110| 00111| 0000000| alu       | Não | and t0, t1, t2               |
| 005   | 01000001111011101000111000110011     | 0110011| 11100| 000    | 11101| 11110| 0100000| alu       | Não | sub t3, t4, t5               |
| 006   | 00000000000000101010010000000011     | 0000011| 01000| 010    | 00101| 00000| 0000000| load      | Não | lw  s0, 0(t0)                |
| 007   | 00000000100001000000001010110011     | 0110011| 00101| 000    | 01000| 01000| 0000000| alu       | Não | add t0, s0, s0               |
| 008   | 11111110010111100000100011100011     | 1100011| 10001| 000    | 11100| 00101| 1111111| branch    | Não | beq t3, t0, inicio           |
| -> 009| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| -> 010| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| 011   | 01000000010111110000111010110011     | 0110011| 11101| 000    | 11110| 00101| 0100000| alu       | Não | sub t4, t5, t0               |
| 012   | 00000000000010010010010010000011     | 0000011| 01001| 010    | 10010| 00000| 0000000| load      | Não | lw  s1, 0(s2)                |
| 013   | 00000110010010100000100110010011     | 0010011| 10011| 000    | 10100| 00100| 0000011| alu       | Não | addi s3, s4, 100             |
| 014   | 00000000000000001000000011100111     | 1100111| 00001| 000    | 00001| 00000| 0000000| jump      | Não | jalr ra                       |
| -> 015| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| -> 016| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| 017   | 11111101110111111111000011101111     | 1101111| 00001| 111    | 11111| 11101| 1111110| jump      | Não | jal ra, inicio               |
| -> 018| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |
| -> 019| 00000000000000000000000000110011     | 0110011| 00000| 000    | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                 |



# SOLUÇÃO 3

| Linha |              Instrução             | opcode |  rd  | funct3|  rs1 |  rs2 | funct7 |    Tipo   | Nop | Risc-V                           |
|-------|-----------------------------------|--------|------|-------|------|------|--------|-----------|-----|-----------------------------------|
| 001   | 00000010100000000000000001101111 | 1101111| 00000| 000   | 00000| 01000| 0000001| jump      | Não | jal zero main                    |
| 002   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |
| 003   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |
| 004   | 00000000011100110111001010110011 | 0110011| 00101| 111   | 00110| 00111| 0000000| alu       | Não | and t0, t1, t2                   |
| 005   | 01000001111011101000111000110011 | 0110011| 11100| 000   | 11101| 11110| 0100000| alu       | Não | sub t3, t4, t5                   |
| 006   | 00000000000000101010010000000011 | 0000011| 01000| 010   | 00101| 00000| 0000000| load      | Não | lw  s0, 0(t0)                    |
| 007   | 00000000100001000000001010110011 | 0110011| 00101| 000   | 01000| 01000| 0000000| alu       | Não | add t0, s0, s0                   |
| 008   | 11111110010111100000100011100011 | 1100011| 10001| 000   | 11100| 00101| 1111111| branch    | Não | beq t3, t0, inicio               |
| 009   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |
| 010   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |
| 011   | 01000000010111110000111010110011 | 0110011| 11101| 000   | 11110| 00101| 0100000| alu       | Não | sub t4, t5, t0                   |
| <span style="color:red;">012</span> | <span style="color:red;">00000000000010010010010010000011</span> | <span style="color:red;">0000011</span> | <span style="color:red;">01001</span> | <span style="color:red;">010</span> | <span style="color:red;">10010</span> | <span style="color:red;">00000</span> | <span style="color:red;">0000000</span> | <span style="color:red;">load</span> | <span style="color:red;">Não</span> | <span style="color:red;">lw  s1, 0(s2)</span> ← Sai daqui   |
| <span style="color:red;">013</span> | <span style="color:red;">00000110010010100000100110010011</span> | <span style="color:red;">0010011</span> | <span style="color:red;">10011</span> | <span style="color:red;">000</span> | <span style="color:red;">10100</span> | <span style="color:red;">00100</span> | <span style="color:red;">0000011</span> | <span style="color:red;">alu</span> | <span style="color:red;">Não</span> | <span style="color:red;">addi s3, s4, 100</span> ← Sai daqui |
| 014   | 00000000000000001000000011100111 | 1100111| 00001| 000   | 00001| 00000| 0000000| jump      | Não | jalr ra                           |
| <span style="color:green;">015</span> | <span style="color:green;">00000000000010010010010010000011</span> | <span style="color:green;">0000011</span> | <span style="color:green;">01001</span> | <span style="color:green;">010</span> | <span style="color:green;">10010</span> | <span style="color:green;">00000</span> | <span style="color:green;">0000000</span> | <span style="color:green;">load</span> | <span style="color:green;">Não</span> | <span style="color:green;">NOP</span> ← Vem para cá              |
| <span style="color:green;">016</span> | <span style="color:green;">00000110010010100000100110010011</span> | <span style="color:green;">0010011</span> | <span style="color:green;">10011</span> | <span style="color:green;">000</span> | <span style="color:green;">10100</span> | <span style="color:green;">00100</span> | <span style="color:green;">0000011</span> | <span style="color:green;">alu</span> | <span style="color:green;">Não</span> | <span style="color:green;">NOP</span> ← Vem para cá              |
| 017   | 11111101110111111111000011101111 | 1101111| 00001| 111   | 11111| 11101| 1111110| jump      | Não | jal ra, inicio                   |
| 018   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |
| 019   | 00000000000000000000000000110011 | 0110011| 00000| 000   | 00000| 00000| 0000000| NOP       | Sim | Inserido NOP                     |


# SOLUÇÃO 4

| Linha | Instrução                            | opcode  | rd    | funct3 | rs1   | rs2   | funct7  | Tipo      | Nop | Risc-V                          |
|-------|--------------------------------------|---------|-------|--------|-------|-------|---------|-----------|-----|----------------------------------|
| 001   | 00000010100000000000000001101111    | 1101111 | 00000 | 000    | 00000 | 01000 | 0000001 | jump      | Não | jal zero main                   |
| 002   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |
| 003   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |
| 004   | 00000000011100110111001010110011    | 0110011 | 00101 | 111    | 00110 | 00111 | 0000000 | alu       | Não | and t0, t1, t2                  |
| <span style="color:red;">005</span> | <span style="color:red;">01000001111011101000111000110011</span> | <span style="color:red;">0110011</span> | <span style="color:red;">11100</span> | <span style="color:red;">000</span> | <span style="color:red;">11101</span> | <span style="color:red;">11110</span> | <span style="color:red;">0100000</span> | <span style="color:red;">alu</span> | <span style="color:red;">Não</span> | <span style="color:red;">sub t3, t4, t5</span> ← Sai daqui      |
| 006   | 00000000000000101010010000000011    | 0000011 | 01000 | 010    | 00101 | 00000 | 0000000 | load      | Não | lw  s0, 0(t0)                   |
| <span style="color:green;">007</span> | <span style="color:green;">01000001111011101000111000110011</span> | <span style="color:green;">0110011</span> | <span style="color:green;">11100</span> | <span style="color:green;">000</span> | <span style="color:green;">11101</span> | <span style="color:green;">11110</span> | <span style="color:green;">0100000</span> | <span style="color:green;">alu</span> | <span style="color:green;">Não</span> | <span style="color:green;">NOP</span> ← Vem para cá               |
| 008   | 00000000100001000000001010110011    | 0110011 | 00101 | 000    | 01000 | 01000 | 0000000 | alu       | Não | add t0, s0, s0                  |
| 009   | 11111110010111100000100011100011    | 1100011 | 10001 | 000    | 11100 | 00101 | 1111111 | branch    | Não | beq t3, t0, inicio              |
| 010   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |
| 011   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |
| 012   | 01000000010111110000111010110011    | 0110011 | 11101 | 000    | 11110 | 00101 | 0100000 | alu       | Não | sub t4, t5, t0                  |
| <span style="color:red;">013</span> | <span style="color:red;">00000000000010010010010010000011</span> | <span style="color:red;">0000011</span> | <span style="color:red;">01001</span> | <span style="color:red;">010</span> | <span style="color:red;">10010</span> | <span style="color:red;">00000</span> | <span style="color:red;">0000000</span> | <span style="color:red;">load</span> | <span style="color:red;">Não</span> | <span style="color:red;">lw  s1, 0(s2)</span> ← Sai daqui      |
| <span style="color:red;">014</span> | <span style="color:red;">00000110010010100000100110010011</span> | <span style="color:red;">0010011</span> | <span style="color:red;">10011</span> | <span style="color:red;">000</span> | <span style="color:red;">10100</span> | <span style="color:red;">00100</span> | <span style="color:red;">0000011</span> | <span style="color:red;">alu</span> | <span style="color:red;">Não</span> | <span style="color:red;">addi s3, s4, 100</span> ← Sai daqui    |
| 015   | 00000000000000001000000011100111    | 1100111 | 00001 | 000    | 00001 | 00000 | 0000000 | jump      | Não | jalr ra                          |
| <span style="color:green;">016</span> | <span style="color:green;">00000000000010010010010010000011</span> | <span style="color:green;">0000011</span> | <span style="color:green;">01001</span> | <span style="color:green;">010</span> | <span style="color:green;">10010</span> | <span style="color:green;">00000</span> | <span style="color:green;">0000000</span> | <span style="color:green;">load</span> | <span style="color:green;">Não</span> | <span style="color:green;">NOP</span> ← Vem para cá               |
| <span style="color:green;">017</span> | <span style="color:green;">00000110010010100000100110010011</span> | <span style="color:green;">0010011</span> | <span style="color:green;">10011</span> | <span style="color:green;">000</span> | <span style="color:green;">10100</span> | <span style="color:green;">00100</span> | <span style="color:green;">0000011</span> | <span style="color:green;">alu</span> | <span style="color:green;">Não</span> | <span style="color:green;">NOP</span> ← Vem para cá               |
| 018   | 11111101110111111111000011101111    | 1101111 | 00001 | 111    | 11111 | 11101 | 1111110 | jump      | Não | jal ra, inicio                  |
| 019   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |
| 020   | 00000000000000000000000000110011    | 0110011 | 00000 | 000    | 00000 | 00000 | 0000000 | NOP       | Sim | Inserido NOP                    |

