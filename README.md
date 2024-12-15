# Turing Machine

## Overview

This project is a simple implementation of a Turing Machine written in PHP. A Turing Machine is a conceptual computational model that can simulate any computer algorithm. This implementation features a randomly generated state and transition tree with every execution, visualizes the machine's state on the terminal, and logs its logical steps for analysis.

## Features

- **Random State and Transition Tree**: Every execution generates a new state and transition tree, output to `states.json` for inspection.
- **Complexity Control**: The user can specify the complexity of the machine, which influences the machine's total number of states.
- Infinite Tape: The machine generates a random tape. Initially, the tape is the same length as the terminal window. As the tape is incremented Left or Right, a new random element will be added to the beginning or end of the tape, as needed, to simulate a tape of infinite length.
- **Visual Display**: The machine's current state, tape, and head are displayed in the terminal in real time.
- **Step Logging**: Every logical step of the machine is logged to `debug.log` for review.

## Prerequisites

- **PHP**: Ensure you have PHP installed on your system. The minimum required version is **PHP 7.4**. You can check the installation by running:
`php -v`

## Usage

### Basic Usage

Run the Turing Machine with the default complexity (4) using the following command:
```
php -f TuringMachine.php
```
### Custom Complexity

To specify a custom complexity for the Turing Machine, pass an integer as an argument. Higher complexity generates more states, resulting in longer and more complex computations.
```
php -f TuringMachine.php 9
```
In this example, the machine will generate a state and transition tree with a higher complexity of 9.

## Files

- **TuringMachine.php**: Main PHP script containing the Turing Machine logic.
- **states.json**: Contains the most recently generated state and transition tree, formatted as JSON.
- **debug.log**: Logs every logical step the machine performs, useful for understanding its operations.

## How It Works

1. **Initialization**:

  - The tape is initialized with a random sequence of `0`, `1`, and `_` symbols.
  - The active element (head) starts at the center of the tape.
  - A random state and transition tree is generated based on the specified complexity.

2. **State and Transition Tree**:

  - The machine generates a number of states (`q0`, `q1`, ..., `qN`, `qH`), where `qH` is the halting state.
  - Each state has a set of rules for each possible tape symbol (`0`, `1`, `_`), determining what symbol to write, in which direction to move the tape (left, right, or none), and which state to transition to next.

3. **Execution**:

  - The machine reads the current tape symbol under the head.
  - It follows the rule defined in the current state's transition table.
  - The symbol is overwritten, the tape moves, and the machine transitions to a new state.
  - The process repeats until the machine enters the halt state (`qH`).

4. **Display**:

  - The terminal displays the current state of the machine, a visual representation of the head, and the visible portion of the tape.

5. **Logging**:

  - Every state transition, tape modification, and movement is logged in `debug.log`.
  - The complete state and transition tree is written to `states.json`.

## Example

Running the following command:
```
php -f TuringMachine.php 5
```
Outputs:

- **Terminal Visualization**: Displays the tape and current state in real time. Here is an example of what the terminal visualization looks like:
```
                                    ┌───────┐
                                    │ q82   │ ┌─┐
                                    └───────┘ └┬┘
_1__0110_011100111_001010_01___01_110001_11000_11__100_10_00_0001_10111_0_111_00_1_0_10_10_1_1
```
- **states.json**: Shows the generated state and transition tree as JSON.
- **debug.log**: Logs every logical step, which might look like this:
```debug.log
  [2024-12-15 18:44:34] Starting Turing machine with initial state: q0
  [2024-12-15 18:44:34] Machine read symbol: "_"
  [2024-12-15 18:44:34] Transition: q0, _ -> 1, L, q4
  [2024-12-15 18:44:35] Machine read symbol: "0"
  [2024-12-15 18:44:35] Transition: q4, 0 -> 0, N, q6
  ...
  [2024-12-15 18:44:43] Transition: q14, 0 -> 0, L, q7
  [2024-12-15 18:44:43] Machine read symbol: "_"
  [2024-12-15 18:44:43] Transition: q7, _ -> _, L, qH
  [2024-12-15 18:44:43] Reached halt state
```
## Customization

- **Complexity**: Adjust complexity by changing the argument to increase/decrease the number of states. The number of states increases exponentially as complexity increases.
- **Symbols**: The set of symbols the tape can contain is defined in the `TuringMachine.php` file (`$this->symbols`).
- **Debug Logging**: Logging can be disabled or redirected to another file by updating the `$logFilePath` in the constructor.

## Possible Enhancements

If there's interest, I may look at implementing enhancements.

- **Pause and Resume**: Add the ability to pause and resume the Turing Machine.
- **Custom Input Tape**: Allow user to define the initial contents of the tape as a command-line argument. I'm not quite sure what this looks like since the tape is effectively infinite. Perhaps this will be an option to fill the tape with a single symbol rather than randomly generating each cell.
- **Custom State Tree**: Allow user to input a custom state transition tree instead of generating one randomly. This could be interesting if someone deeper into the study or teaching of computation would have a use for this.
- **Colorized Output**: Use ANSI escape codes to colorize the output, highlighting the head or current state. Will probably do this at some point soon. Colors are fancy and I like fancy.

## Learn More

A Turing Machine is a fundamental concept in computer science, proposed by Alan Turing in 1936. It provides a simple model for understanding computation and has profound implications for the theory of computation, algorithms, and the limits of what can be computed. To learn more about Turing Machines, consider exploring the following resources:

- [Wikipedia: Alan Turing](https://en.wikipedia.org/wiki/Alan_Turing)
- [Wikipedia: Turing Machine](https://en.wikipedia.org/wiki/Turing_machine)

## License

This project is licensed under the MIT License. Feel free to use, modify, and distribute it as you wish.

---

Enjoy exploring the fascinating world of Turing Machines!
