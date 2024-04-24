<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

 $queue_name = 'confirms_queue';


try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    $channel = $connection->channel();

    $callback = function ($msg) {
        echo ' [x] Received ', $msg->body, "\n";
        $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], false);
    };

    $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo '13134' . $e->getMessage();
}
