<?php
// Include the definition file
include_once 'dynDirs.php';
include_once 'api_key.php';

// Output for $m variables
for ($i = 1; $i <= 100; $i++) {
    $mVariable = 'mdl' . $i; 
    echo "\$mdl$i . \"/\" is simulated by " . ${$mVariable} . "/\n"; 
}

// echo "\n"; // Add a line break for separation

// Output for $n variables
for ($i = 1; $i <= 100; $i++) {
    $nVariable = 'n' . $i; 
    echo "\$n$i . \"/\" is simulated by " . ${$nVariable} . "/\n"; 
}

echo "\n"; // Add a line break for separation

// Output for combined variables
for ($i = 1; $i <= 100; $i++) {
    $mVar = 'mdl' . $i; 
    $nVar = 'n' . $i;

    // Combine the variable names
    $mnVar = $mVar . $nVar;

    // Output using variable variables to access the actual values
    echo "\$mnVar$i . \"/\" is simulated by " . ${$mVar} . ${$nVar} . "/\n"; 
}

echo $mdl10. $n10. $mdl100 . "/";
// echo $z1;
// echo $n1;

