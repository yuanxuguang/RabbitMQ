<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * direct 交换机类型
 * 是一个完全依赖路由键的路由模式。
 */
try {
    // 交换机名称
    $exchange = 'topic_exchange';

    $queue_name1 = 'queue_topic_1';
    $queue_name2 = 'queue_topic_2';
    $queue_name3 = 'queue_topic_3';
    $queue_name4 = 'queue_topic_4';

    /**
     * 路由键
     *    *(星号)可以代替一个单词 , #(井号)可以替代零个或多个单词
     */
    $routing_key1 = 'admin.*.rabbit';   
    $routing_key2 = 'admin.#';
    $routing_key3 = '*.admin';
    $routing_key4 = '#.admin';

    // 连接RabbitMq
    $connection = new AMQPStreamConnection('localhost', 5672, 'admin', 'admin');

    // 创建通道
    $channel = $connection->channel();

    /**
     * 声明 direct 类型的交换器
     * 1. 这是你要声明的交换机的名字。
     * 2. 这是交换机的类型，可以是 'direct', 'topic', 'headers' 或 'fanout'。
     * 3. 第三个参数是 passive。当设置为 true，如果交换机已经存在，那么就会返回详情，在交换机不存在的情况下则会抛出错误。当设置为 false，如果交换机不存在，则会新建一个，如果存在，则直接返回详情。
     * 4. 第四个参数是 durable。当设置为 true，表示交换机将在 RabbitMQ 服务器重启后依然存在。
     * 5. 第五个参数是 auto_delete。当设置为 true，表示当所有队列都完成对该交换机的使用后，RabbitMQ将自动删除这个交换机。
     */
    $channel->exchange_declare($exchange, 'topic', true, true, false);

    /**
     * 创建队列
     * 1. 队列名称
     * 2. $passive 如果此参数为 true，在服务器上不存在名为 $queue 的队列的话，则不会创建队列，如果存在，则正常声明，并且不会影响已存在的队列，如果不存在则会返回错误。如果此参数也为 false，在服务器中不存在名为 $queue 的队列的话，就会创建一个新的队列。
     * 3. $durable 如果设置为 true，该队列将持久化，即重启 RabbitMQ 服务后仍然存在，但队列中的消息会根据消息的投递模式（delivery mode）决定是否持久化。
     * 4. $exclusive 如果设置为 true，该队列将变成私有队列，仅供此连接使用。队列的排他性会在连接断开时自动删除，无论是否设置了 auto-delete。
     * 5. $auto_delete 如果设置为 true，在最后一个消费者断开连接后，这个队列会自动被删除。如果同时还设置了 exclusive，则排他性优先。
     */
    $channel->queue_declare($queue_name1, false, false, false, false);
    $channel->queue_declare($queue_name2, false, false, false, false);
    $channel->queue_declare($queue_name3, false, false, false, false);
    $channel->queue_declare($queue_name4, false, false, false, false);
 
    /**
     * 绑定队列到交换机
     * 1. 队列名称
     * 2. 交换机名称
     * 3. 路由键 topic 类型的路由键模糊匹配
     */
    $channel->queue_bind($queue_name1, $exchange, $routing_key1);
    $channel->queue_bind($queue_name2, $exchange, $routing_key2);
    $channel->queue_bind($queue_name3, $exchange, $routing_key3);
    $channel->queue_bind($queue_name4, $exchange, $routing_key4);

    // 将消息发布到交换机, 路由key1
    $channel->basic_publish(new AMQPMessage('rabbit message'), $exchange, 'admin.white.rabbit');
    $channel->basic_publish(new AMQPMessage('rabbit.child message'), $exchange, 'admin.white.rabbit.child');
    $channel->basic_publish(new AMQPMessage('black.admin message'), $exchange, 'black.admin');
    $channel->basic_publish(new AMQPMessage('orange.red.admin message'), $exchange, 'orange.red.admin');
 
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo $e->getMessage();
}

echo 'success';