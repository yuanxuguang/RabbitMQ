<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 消息应答
 * 消费者完成一个任务可能需要一段时间，如果其中一个消费者处理一个长的任务并仅只完成
 * 了部分突然它挂掉了，会发生什么情况。RabbitMQ一旦向消费者传递了一条消息，便立即将该消
 * 息标记为删除。在这种情况下，突然有个消费者挂掉了，我们将丢失正在处理的消息。以及后续
 * 发送给该消费这的消息，因为它无法接收到。
 * 为了保证消息在发送过程中不丢失，rabbitmq引入消息应答机制，消息应答就是:消费者在接
 * 收到消息并且处理该消息之后，告诉rabbitmq它已经处理了，rabbitmq可以把该消息删除了。
 */

 $exchange = 'direct_exchange';

 $queue_name = 'ack_queue';

 // 路由键
 $routing_key = 'ack_key';

try {
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    $channel = $connection->channel();

    $channel->queue_declare($queue_name, false, false, false, false);

    echo ' [*] Waiting for messages. To exit press CTRL+C';

    $callback = function ($msg) {
        echo ' [x] Received ', $msg->body, "\n";
        // ack 肯定确认消息 可以批量确认 
        // $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag'], false);
        // ack 否定确认 可以批量确认
        // $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
        // basic_reject: 否定确认消息已成功处理，仅限于一条消息。 basic_reject 第二个参数是requeue
         $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], false);
        // 注意：上述第二和第三个参数，分别代表 multiple 和 requeue，具体你可以根据需要来设定。第二个参数 multiple，表示是否批量进行消息确认；第三个参数 requeue，如果设置为 true ，RabbitMQ 会重新将这条消息放回队列尾部，如果是 false ，RabbitMQ 会直接丢掉这条消息。
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
