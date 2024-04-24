## 消息应答

#### 1.1 概念
    消费者完成一个任务可能需要一段时间，如果其中一个消费者处理一个长的任务并仅只完成
    了部分突然它挂掉了，会发生什么情况。RabbitMQ一旦向消费者传递了一条消息，便立即将该消
    息标记为删除。在这种情况下，突然有个消费者挂掉了，我们将丢失正在处理的消息。以及后续
    发送给该消费这的消息，因为它无法接收到。 
    为了保证消息在发送过程中不丢失，rabbitmq引入消息应答机制，消息应答就是:消费者在接
    收到消息并且处理该消息之后，告诉rabbitmq它已经处理了，rabbitmq可以把该消息删除了。

#### 1.2 自动应答
    消息发送后立即被认为已经传送成功，这种模式需要在高吞吐量和数据传输安全性方面做权
    衡,因为这种模式如果消息在接收到之前，消费者那边出现连接或者channel关闭，那么消息就丢
    失了,当然另一方面这种模式消费者那边可以传递过载的消息，没有对传递的消息数量进行限制，
    当然这样有可能使得消费者这边由于接收太多还来不及处理的消息，导致这些消息的积压，最终
    使得内存耗尽，最终这些消费者线程被操作系统杀死，所以这种模式仅适用在消费者可以高效并
    以某种速率能够处理这些消息的情况下使用。
    **默认消息采用的是自动应答 **

#### 1.3 消息应答的方法
    A.Channel.basicAck(用于肯定确认) 
    RabbitMQ 已知道该消息并且成功的处理消息，可以将其丢弃了 
    B.Channel.basicNack(用于否定确认) 
    C.Channel.basicReject(用于否定确认) 
    与Channel.basicNack 相比少一个参数 
    不处理该消息了直接拒绝，可以将其丢弃了

    basic_ack：这是一个确认消费的方法，表示消费者成功处理了消息，RabbitMQ 可以从队列中移除此条消息。在手动确认模式下，这个方法由消费者代码负责调用。
    basic_nack：这是一个否定确认的方法，表示消费者处理消息失败。basic_nack 的一个优点是它支持批量拒绝，可以一次拒绝多条消息。与此同时，basic_nack 还允许你决定是否将未成功消费的消息重新入队。
    basic_reject：这跟 basic_nack 非常类似，都是表示消费者处理消息失败。然而，basic_reject 与 basic_nack 的主要区别在于，basic_reject 不支持批量操作，它只能拒绝单条消息。它也允许你决定是否将未成功消费的消息重新入队。

#### 1.4 Multiple的解释 
手动应答的好处是可以批量应答并且减少网络拥堵 
    
    Channel.basicAck(deliveryTag, multiple = true/false)
    multiple 的 true 和 false 代表不同意思 
    true 代表批量应答channel上未应答的消息 
    false 同上面相比 
    比如说channel上有传送tag的消息 5,6,7,8 当前tag是8 那么此时 
    5-8 的这些还未应答的消息都会被确认收到消息应答 
    只会应答tag=8的消息 5,6,7这三个消息依然不会被确认收到消息应答

#### 1.5 消息自动重新入队 
    如果消费者由于某些原因失去连接(其通道已关闭，连接已关闭或TCP连接丢失)，导致消息
    未发送ACK确认，RabbitMQ将了解到消息未完全处理，并将对其重新排队。如果此时其他消费者
    可以处理，它将很快将其重新分发给另一个消费者。这样，即使某个消费者偶尔死亡，也可以确
    保不会丢失任何消息。 

#### 1.6 消息手动应答代码  
```PHP
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
```