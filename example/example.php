<?php

$hostname = "127.0.0.1";
$port_number = 8125;

$statsd = new StatsD($hostname, $port_number);

for($i = 0; $i < 18000; $i++) {
	//<game>.<topic>.<counter_name>
	$statsd->increment('high_level_topic.test.test_counter');
	if ($i % 100 == 0) {
		printf("#");
		sleep(2);
	} else if ($i % 1000 == 0) {
		printf("*");
		sleep(10);
	} else {
		printf(".");
		sleep(0.8);
	}
}

printf("\n");