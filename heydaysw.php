<?php
	header("Service-Worker-Allowed: /");
	header("Content-Type: application/javascript");
    echo "importScripts('https://cdn.heyday.io/heyDaySw.js');";
