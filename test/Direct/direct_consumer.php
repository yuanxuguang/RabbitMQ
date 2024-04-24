<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

 // 交换机名称
 $exchange = 'direct_exchange';

 $queue_name1 = 'queue_1';
 $queue_name2 = 'queue_2';
 $queue_name3 = 'queue_3';
 $queue_name4 = 'queue_4';

 // 路由键
 $routing_key1 = 'key_1';
 $routing_key2 = 'key_2';
 $routing_key3 = 'key_3';

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    $channel = $connection->channel();

    $channel->queue_declare($queue_name4, false, false, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C';

    $callback = function ($msg) {
        echo ' [x] Received ', $msg->body, "\n";
    };

    $channel->basic_consume($queue_name4, '', false, true, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo '13134' . $e->getMessage();
}
