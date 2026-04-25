<?php
$cleaned = "{\n  \"score\": 90,\n  \"reason\": \"O U,O3USOOc O U,OO OUSOc OO,UO OrO\"OOc O1U.U,USOc U.U.OO OOc O\"} ";

if (preg_match('/["\']?reason["\']?\s*:\s*"(.*?)"\s*[,}]/s', $cleaned, $reasonMatch)) {
    var_dump("Pattern 1 matched:", trim($reasonMatch[1]));
} else {
    var_dump("Pattern 1 failed");
}
