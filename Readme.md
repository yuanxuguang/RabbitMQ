- 一：MQ 基本概念
    
    
    MQ(message queue)，从字面意思上看就个 FIFO 先入先出的队列，只不过队列中存放的内容是 message 而已，它是一种具有接收数据、存储数据、发送数据等功能的技术服务。
    
    ![Untitled](https://prod-files-secure.s3.us-west-2.amazonaws.com/5dbde416-fdc8-4096-8f9b-5aaa6c3ef508/57cc3282-c83f-4690-bbba-3f292565e0ed/Untitled.png)
    
    ![Untitled](https://prod-files-secure.s3.us-west-2.amazonaws.com/5dbde416-fdc8-4096-8f9b-5aaa6c3ef508/44851200-dbdb-4b92-abfe-0f0e1648ad46/Untitled.png)
    
- 为啥要用MQ
    1.  高并发的流量削峰 
    2. 应用解耦
    3. 异步处理
    4. 分布式事务
    5. **数据分发**
- **架构组成**
    
    ![Untitled](https://prod-files-secure.s3.us-west-2.amazonaws.com/5dbde416-fdc8-4096-8f9b-5aaa6c3ef508/02ca7b04-d621-485e-8da8-b87dd0a2f82f/Untitled.png)
    
    - `Broker`：就是 RabbitMQ 服务，用于接收和分发消息，接受客户端的连接，实现 AMQP 实体服务。
    - `Virtual host`：出于多租户和安全因素设计的，把 AMQP 的基本组件划分到一个虚拟的分组中，类似于网络中的 namespace 概
    
    念。当多个不同的用户使用同一个 RabbitMQ server 提供的服务时，可以划分出多个 vhost，每个用户在自己的 vhost 创建 exchange 或 queue 等。
    
    - `Connection`：连接，生产者/消费者与 Broker 之间的 TCP 网络连接。
    - `Channel`：网络信道，如果每一次访问 RabbitMQ 都建立一个 Connection，在消息量大的时候建立连接的开销将是巨大的，效率也
    
    较低。Channel 是在 connection 内部建立的逻辑连接，如果应用程序支持多线程，通常每个 thread 创建单独的 channel 进行通讯，AMQP method 包含了 channel id 帮助客户端和 message broker 识别 channel，所以 channel 之间是完全隔离的。Channel 作为轻量级的Connection 极大减少了操作系统建立 TCP connection 的开销。
    
    - `Message`：消息，服务与应用程序之间传送的数据，由Properties和body组成，Properties可是对消息进行修饰，比如消息的优先
    
    级，延迟等高级特性，Body则就是消息体的内容。
    
    - `Virtual Host`：虚拟节点，用于进行逻辑隔离，最上层的消息路由，一个虚拟主机理由可以有若干个Exhange和Queue，同一个虚
    
    拟主机里面不能有相同名字的Exchange
    
    - `Exchange`：交换机，是 message 到达 broker 的第一站，用于根据分发规则、匹配查询表中的 routing key，分发消息到 queue 中
    
    去，不具备消息存储的功能。常用的类型有：direct、topic、fanout。
    
    - `Bindings`：exchange 和 queue 之间的虚拟连接，binding 中可以包含 routing key，Binding 信息被保存到 exchange 中的查询表
    
    中，用于 message 的分发依据。
    
    - `Routing key`：是一个路由规则，虚拟机可以用它来确定如何路由一个特定消息
    - `Queue`：消息队列，保存消息并将它们转发给消费者进行消费。
- **核心概念**
    - **生产者**：产生数据发送消息的程序是生产者。
    - **交换机**：交换机是 RabbitMQ 非常重要的一个部件，一方面它接收来自生产者的消息，另一方面它将消息推送到队列中。交换机必须确切知道如何处理它接收到的消息，是将这些消息推送到特定队列还是推送到多个队列，亦或者是把消息丢弃，这个是由交换机类型决定的。
    - **队列**：队列是 RabbitMQ 内部使用的一种数据结构，尽管消息流经 RabbitMQ 和应用程序，但它们只能存储在队列中。队列仅受主机的内存和磁盘限制的约束，本质上是一个大的消息缓冲区。许多生产者可以将消息发送到一个队列，许多消费者可以尝试从一个队列接收数据。
    - **消费者**：消费与接收具有相似的含义。消费者大多时候是一个等待接收消息的程序。请注意生产者，消费者和消息中间件很多时候并不在同一机器上。同一个应用程序既可以是生产者又是可以是消费者。
    
    **其中交换机分为四种类型**
    >>> 在RabbitMQ中，消息是从生产者发送到交换机，然后由交换机根据路由键的设置来决定将消息发送到哪些队列。所以通常我们在发布消息时，会声明一个交换机，设置一个路由键，然后发布消息到这?个交换机。但在RabbitMQ中，除了自定义的交换机之外，还存在一个特殊的默认交换机，路由键就是真正的目标队列的名字。当我们在发布消息时未显式指定交换机，消息就会被发布到这个默认的交换机，然后根据我们设定的路由键（实际就是队列名）将消息发送到对应的队列。 
    *1. 直接队列（Direct Queue）***：默认类型，一个消息被发送到 direct 的交换机并带有一个路由键（routing key）。这个消息将被投递到 binding key 与 routing key 完全匹配的队列。
    *2. 主题队列（Topic Queue）*消息发送到 topic 类型的交换机，并带有一个路由键（例：`"animals.rabbit"`）。这个消息会被路由到 binding key 与 routing key 符合某种模式匹配的队列（例：`"*.rabbit" 或 "animals.*"`)
    *3. 扇形队列***（Fanout Queue）把所有发送到该交换机的消息路由到所有和该交换机绑定的队列。路由键不影响扇形交换机的路由规则。
    *4. 头部队列***（Headers Queue）头部队列不依赖于路由键的模式匹配规则，而是根据发送的消息内容中的 headers 属性进行匹配。在绑定一个 Headers 队列的时候，可以设定任意数量的键值对，消息的 headers 和队列绑定的键值对如果都匹配，那么消息就被路由到该队列。
    
- 安装 （ubuntu）
    1. 安装RabbitMQ 前 需要先安装erlang
    
    ```php
    sudo apt-get install erlang-nox
    ```
    
    1. 安装RabbitMQ
    
    ```
    wget -O- https://www.rabbitmq.com/rabbitmq-release-signing-key.asc | sudo apt-key add -
    sudo apt-get update
    sudo apt-get install rabbitmq-server
    ```
    
    安装完成后，可以使用systemctl 命令查看运行状态
    
    `systemctl status rabbitmq-server`
    
    ![Untitled](https://prod-files-secure.s3.us-west-2.amazonaws.com/5dbde416-fdc8-4096-8f9b-5aaa6c3ef508/5008360a-a2d0-4bdc-863f-e0dd9dca4503/Untitled.png)