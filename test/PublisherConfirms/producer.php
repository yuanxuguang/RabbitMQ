<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');
$channel = $connection->channel();
$channel->confirm_select(); // 开启发布确认模式

$queue_name = 'confirms_queue';

$channel->queue_declare($queue_name, false, true, false, false);

$msg_body = 'Hello, RabbitMQ - Publisher Confirms';
$msg = new AMQPMessage($msg_body, array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

$channel->basic_publish($msg, '', $queue_name);

// 定义确认接收和未确认接收的回调函数
$channel->set_ack_handler(function ($message) {
    echo 'Message is confirmed!' . PHP_EOL;
});
$channel->set_nack_handler(function ($message) {
    echo 'Message could not be confirmed:' . $message->body . PHP_EOL;
});
// 处理确认信息
$res = $channel->wait_for_pending_acks_returns(5);
var_dump($res);

$channel->close();
$connection->close();
?>