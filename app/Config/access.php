<?php
// Include the definition file
require_once 'dynDirs.php';

// Output for $m variables
for ($i = 1; $i <= 10; $i++) {
    $mVariable = 'm' . $i; 
    echo "\$m$i . \"/\" is simulated by " . ${$mVariable} . "/\n"; 
}

echo "\n"; // Add a line break for separation

// Output for $n variables
for ($i = 1; $i <= 10; $i++) {
    $nVariable = 'n' . $i; 
    echo "\$n$i . \"/\" is simulated by " . ${$nVariable} . "/\n"; 
}

echo "\n"; // Add a line break for separation

// Output for combined variables
for ($i = 1; $i <= 10; $i++) {
    $mVar = 'm' . $i; 
    $nVar = 'n' . $i;

    // Combine the variable names
    $mnVar = $mVar . $nVar;

    // Output using variable variables to access the actual values
    echo "\$mnVar$i . \"/\" is simulated by " . ${$mVar} . ${$nVar} . "/\n"; 
}
