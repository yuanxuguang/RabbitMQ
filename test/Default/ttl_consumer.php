<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

 // 交换机名称
 $exchange = 'dead_exchange';

// $queue_name = 'normal_queue_name';
$queue_name = 'dead_queue_name';


try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    $channel = $connection->channel();

    echo ' [*] Waiting for messages. To exit press CTRL+C';

    $callback = function ($msg) {
        echo ' [x] Received ', $msg->body, "\n";
    };  

    $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo '13134' . $e->getMessage();
}
