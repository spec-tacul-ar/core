<?php

use Illuminate\Http\Request;
use function Laravel\Folio\render;

render(function (Request $request) {
    return redirect('/docs/ai#connect-agents');
});

?>
