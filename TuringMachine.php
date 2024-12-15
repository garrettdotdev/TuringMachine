<?php

class TuringMachine
{
  public array $states;
  private array $tape;
  private int $activeElementIndex;
  private string $currentState;
  private int $terminalWidth;
  private array $cursorPositions;
  private array $symbols;

  private string $logFilePath;

  public function __construct(int $complexity, string $logFilePath = 'debug.log')
  {
    $this->symbols = ['0', '1', '_'];
    $this->terminalWidth = $this->getTerminalWidth();
    $this->initializeTape();
    $this->activeElementIndex = intdiv(count($this->tape), 2); // Center the active element
    $this->states = $this->generateStates($complexity);
    $this->currentState = $this->getRandomStateName();
    $this->cursorPositions = [];
    $this->logFilePath = $logFilePath;
    file_put_contents('states.json', json_encode($this->states, JSON_PRETTY_PRINT));
  }

  public function log($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
  }

  private function getRandomStateName(): string
  {
    $filteredKeys = array_filter(array_keys($this->states), function($key) {
      return $key !== 'qH';
    });
    return $filteredKeys[array_rand($filteredKeys)];
  }

  private function initializeTape(): void
  {
    $symbols = $this->symbols;
    $this->tape = array_map(function() use ($symbols) {
      return $symbols[array_rand($symbols)];
    }, array_fill(0, $this->terminalWidth, '_'));
  }

  private function getTerminalWidth(): int
  {
    $stty_size = shell_exec('stty size');
    list($rows, $cols) = explode(' ', $stty_size);
    return (int) $cols;
  }

  private function getCursorPosition(): array {
    shell_exec('stty -icanon -echo');
    echo "\033[6n";
    // Read the response from the terminal
    $response = '';
    while (true) {
      $char = fread(STDIN, 1);
      $response .= $char;
      if ($char === 'R') break;
    }
    // Extract row and column from the response
    if (preg_match('/\[(\d+);(\d+)R/', $response, $matches)) {
      return [
        'row' => (int) $matches[1],
        'col' => (int) $matches[2]
      ];
    }
    return ['row' => 0, 'col' => 0]; // Fallback if parsing fails
  }

  private function moveCursorPosition(array $position): void {
    printf("\033[%d;%dH", $position['row'], $position['col']);
  }

  private function generateStates(int $complexity): array
  {
    $numStates = (int) 2 ** (1 + $complexity);
    $states = [];
    $directions = ['L', 'R', 'N', 'L'];
    $availableStates = range(0, $numStates - 1);
    $availableStates[] = 'H'; // Halt state

    for ($i=0; $i < $numStates; $i++) {
      $stateName = 'q' . $i;
      $states[$stateName] = [];
      foreach ($this->symbols as $symbol) {
        $writeSymbol = $this->symbols[array_rand($this->symbols)];
        $moveDirection = $directions[array_rand($directions)];
        $nextState = 'q' . $availableStates[array_rand($availableStates)];
        $states[$stateName][$symbol] = [$writeSymbol, $moveDirection, $nextState];
      }
    }
    $states['qH'] = []; // Halt state
    return $states;
  }

  private function getStateName(array $state): string
  {
    return array_key_first($state);
  }

  private function moveTape(string $direction): void
  {
    switch ($direction) {
      case 'L':
        $this->activeElementIndex++;
        if( $this->activeElementIndex + intdiv($this->terminalWidth, 2) >= count($this->tape) ) {
          $this->tape[] = $this->symbols[array_rand($this->symbols)];
        }

        break;
      case 'R':
        $this->activeElementIndex = max(0, $this->activeElementIndex - 1);
        if( $this->activeElementIndex - intdiv($this->terminalWidth, 2) < 0 ) {
          array_unshift($this->tape, $this->symbols[array_rand($this->symbols)]);
          $this->activeElementIndex++;
        }
        break;
      case 'N':
        // Do nothing, active element stays in the same position
        break;
      default:
        throw new InvalidArgumentException("Invalid move direction: $direction");
    }
  }

  public function run(): void
  {
    $this->log("Starting Turing machine with initial state: $this->currentState");
    $this->initializeDisplay();
    usleep(200000);
    while (true) {
      if($this->currentState === 'qH') {
        $this->log("Reached halt state");
        $this->updateDisplay();
        break;
      } // Halting condition: current state is the halt state

      $currentSymbol = $this->tape[$this->activeElementIndex];
      $this->log("Machine read symbol: \"$currentSymbol\"");

      if (!isset($this->states[$this->currentState][$currentSymbol])) {
        $this->log("No rule for state $this->currentState and symbol \"$currentSymbol\"");
        break; // Halting condition: no rule for the current state and symbol
      }

      [$writeSymbol, $moveDirection, $nextState] = $this->states[$this->currentState][$currentSymbol];
      $this->log("Transition: $this->currentState, $currentSymbol -> $writeSymbol, $moveDirection, $nextState");

      // Write symbol on tape
      $this->tape[$this->activeElementIndex] = $writeSymbol;

      $this->updateDisplay("Write symbol: $writeSymbol");
      usleep(200000); // Slow down the display for better visualization

      // Move tape
      $this->moveTape($moveDirection);

      $this->updateDisplay("Move tape $moveDirection");
      usleep(200000); // Slow down the display for better visualization

      // Transition to next state
      $this->currentState = $nextState;

    }
  }

  private function initializeDisplay(): void
  {
    $tapeSegment = $this->getTapeSegment();
    $tapeLine = implode('', $tapeSegment);

    $qDisplayLine1 = '┌───────┐';
    $qDisplayEdge = '│';
    $qDisplay = $this->currentState . str_repeat(' ', 5 - mb_strlen($this->currentState, 'UTF-8'));
    $qDisplayLine3 = '└───────┘';
    $headOffset = intdiv($this->terminalWidth,2) - 1;
    $headLine1 = "┌─┐";
    $headLine2 = "└┬┘";
    $qDisplayOffset = intdiv($this->terminalWidth, 2) - mb_strlen($qDisplayLine3, 'UTF-8') - mb_strlen($headLine2, 'UTF-8') + 1;

    $this->cursorPositions['start'] = $this->getCursorPosition();
    // Display line 1
    printf("%s%s" . PHP_EOL, str_repeat(' ',$qDisplayOffset), $qDisplayLine1);
    // Display line 2
    printf("%s%s", str_repeat(' ',$qDisplayOffset), $qDisplayEdge . ' ');
    $this->cursorPositions['qDisplay'] = $this->getCursorPosition();
    printf("%s", $qDisplay);
    printf(" %s %s" . PHP_EOL, $qDisplayEdge, $headLine1);
    // Display line 3
    printf("%s %s %s" . PHP_EOL, str_repeat(' ',$qDisplayOffset - 1), $qDisplayLine3, $headLine2);
    $this->cursorPositions['tapeLine'] = $this->getCursorPosition();
    printf("%s" . PHP_EOL, $tapeLine);
  }

  private function updateDisplay(): void {
    $tapeSegment = $this->getTapeSegment();
    $tapeLine = implode('', $tapeSegment);
    $this->moveCursorPosition($this->cursorPositions['qDisplay']);
    printf("%s", $this->currentState . str_repeat(' ', 5 - mb_strlen($this->currentState, 'UTF-8')));
    $this->moveCursorPosition($this->cursorPositions['tapeLine']);
    printf("%s", $tapeLine);
  }

  private function getTapeSegment(): array {
    $start = max(0, $this->activeElementIndex - intdiv($this->terminalWidth, 2));
    return array_slice($this->tape, $start, $this->terminalWidth);
  }
}

// Create and run the Turing machine
$complexity = isset($argv[1]) ? (int) $argv[1] : 4; // Default complexity: 4
$turingMachine = new TuringMachine($complexity);
$turingMachine->run();