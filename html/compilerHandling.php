<?php
function addDefaultAdditionalFlags(&$additional, $compiler) {
  /* Set to the intel syntax */
  if ($compiler === "gcc" && preg_match('/^-masm=/', $additional) == 0) {
    $additional = $additional . " -masm=intel ";
  }
  if (preg_match('/^-fno-asynchronous-unwind-tables/', $additional) == 0) {
    $additional = $additional . " -fno-asynchronous-unwind-tables ";
  }
}

function printListing($listing) {
  echo PHP_EOL;
  foreach ($listing as $listing_line) {
    echo $listing_line . PHP_EOL;
  }
}

function isCompilerGood($compiler) {
  $compilers = array(
    'avr-gcc',
    'gcc',
    'arm-none-eabi-gcc',
    'arm-linux-gnueabi-gcc',
    'mips-linux-gnu-gcc'
  );

  return in_array($compiler, $compilers, true);
}

?>
