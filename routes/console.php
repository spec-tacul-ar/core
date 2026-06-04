<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('passport:purge --hours=6')->hourly();
