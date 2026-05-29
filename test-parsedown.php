<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/parsedown/Parsedown.php';
$pd = new Parsedown();
$pd->setBreaksEnabled(true);

// Test Parsedown behavior with $$...$$ and underscores
$tests = [
    'No $$: {}_2 F_1 test',
    'With $$ same line: $${}_2 F_1$$',
    'With $$ and newlines (no blank line): $$\n{}_2 F_1\n$$',
    'With $$ and blank lines:\n\n$$\n\n{}_2 F_1\n\n$$\n\nend',
    'matrix with & and \\\\: $$\n\\begin{bmatrix}\na & b \\\\\nc & d\n\\end{bmatrix}$$',
];

foreach ($tests as $label => $input) {
    // Use _ as the separator - actually let me just print them
}
// Manual test
echo "=== Test: $$ with blank lines ===\n";
$input = "before\n\n$$\n\n{}_2 F_1(a, b; c; z)\n\n$$\n\nend";
echo "INPUT:\n$input\n\nOUTPUT:\n" . $pd->text($input) . "\n\n";

echo "=== Test: matrix with & and \\\\ ===\n";
$input2 = "before\n\n$$\n\\begin{bmatrix}\na & b \\\\\nc & d\n\\end{bmatrix}\n$$\n\nend";
echo "INPUT:\n$input2\n\nOUTPUT:\n" . $pd->text($input2) . "\n\n";
