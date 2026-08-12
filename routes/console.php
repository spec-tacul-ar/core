<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('passport:purge --hours=6')->hourly();
Schedule::command('spectacular:oauth-clean')->hourly();
Schedule::command('queue:prune-failed --hours=24')->hourly();
