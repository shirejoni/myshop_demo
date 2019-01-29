<?php

namespace App\Web\Controller;

use App\System\Controller;

class ControllerError extends Controller {
    public function notFound() {
        echo "<br>";
        echo "Not Found Error in <b>ControllerError</b>/notFound: 7";
        echo "<br>";
    }
}