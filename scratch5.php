<?php
$cleaned = "{   \"score\": 90,   \"reason\": \"O U,O3USOOc O U,OO OUSOc OO,UO OrO\"OOc O1U.U,USOc U.U.OO OOc O\"} ";

if (preg_match('/["\']?score["\']?\s*:\s*(\d+)/i', $cleaned, $scoreMatch)) {
    var_dump("Score matched:", $scoreMatch[1]);
} else {
    var_dump("Score failed");
}
