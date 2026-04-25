<?php
$cleaned_content = '{   "score": 88,   "reason": "USOU.OO1 O U,U.OU,O_U. O"OrO"OOc O1U.U,USOc U.U.OO OOc OOUSO_"} ';

if (preg_match('/["\']?reason["\']?\s*:\s*"(.*?)"\s*[,}]/s', $cleaned_content, $reasonMatch)) {
    echo "Pattern 1 Matched: " . trim($reasonMatch[1]) . "\n";
} else {
    echo "Pattern 1 Failed\n";
}
