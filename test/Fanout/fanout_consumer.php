<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// 交换机名称
$exchange = 'fanout_exchange';

$queue_name1 = 'queue_fanout_1';
$queue_name2 = 'queue_fanout_2';

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    $channel = $connection->channel();

    $channel->queue_declare($queue_name1, false, false, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C';

    $callback = function ($msg) {
        echo ' [x] Received ', $msg->body, "\n";
    };

    $channel->basic_consume($queue_name1, '', false, true, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo '13134' . $e->getMessage();
}
