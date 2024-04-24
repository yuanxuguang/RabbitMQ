<?php
require_once '../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**                                                                             
 * 超时队列 
 * 超时队列未执行的可以保存到私信队列，如果没有私信队列则丢失
 * 
 */
try {
    // 正常交换机，队列，路由键
    $normal_exchange = 'normal_exchange';
    $normal_queue_name = 'normal_queue_name';
    $normal_key = 'normal_key';

    /**
     * 死信交换机，队列名称，路由键
     */
    $dead_exchange = 'dead_exchange';

    $dead_queue_name = 'dead_queue_name';

    $dead_key  = 'dead_key ';

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

    /**
     * 声明正常交换机
     * 声明正常队列
     * 绑定正常交换机到正常队列
     */
    $channel->exchange_declare($normal_exchange, 'direct', false, true, false);    
    $args = array(
        'x-dead-letter-exchange' => ['S', $dead_exchange],
        "x-dead-letter-routing-key" => ["S", $dead_key]
    );
    $channel->queue_declare($normal_queue_name, false, true, false, false, false, $args); 
    $channel->queue_bind($normal_queue_name, $normal_exchange, $normal_key);

     /**
     * 声明死信交换机
     * 声明死信队列
     * 绑定死信交换机到死信队列
     */
    $channel->exchange_declare($dead_exchange, 'direct', false, true, false);
    $channel->queue_declare($dead_queue_name, false, true, false, false); 
    $channel->queue_bind($dead_queue_name, $dead_exchange, $dead_key);

    // 将消息发布到交换机, 设置过期时间，过期时间为毫秒。过期不消费如果有私信队列，则进入私信队列。无死信队列则丢失
    $channel->basic_publish(new AMQPMessage('key_ttl message', ['expiration' => 15000]), $normal_exchange, $normal_key);
 
    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo $e->getMessage();
}

echo 'success';