#通过php实现grpc服务端
我用的php版本：
    php 8.2

参考文档：

https://roadrunner.dev/docs/plugins-grpc/2023.x/en

1 根据自己的需求创建Protobuf的 simple.proto  文件

2 通过 simple.proto 生成 php 类文件

    这里需要用到下载两个可执行文件编译，我这里用的是win10：
    protoc.exe 和 protoc-gen-php-grpc.exe 
    下载地址：https://github.com/roadrunner-server/roadrunner/releases
    命令行执行如下命令：
    ./protoc.exe --plugin=protoc-gen-php-grpc.exe --php_out=./ --php-grpc_out=./ HelloWorld.proto
    会在当前目录下生成两个文件夹：GPBMetadata 和 Services
    把生成好的文件夹复制到 项目目录 grpc/generated 下
```
root
├─app
│  ├─console 
│  ├─services        
│  │   ├─Task        //异步任务
│  │   └─HelloService.php   //写自己的业务
│  └─support
├─config
├─grpc
│ ├─generated         //使用protoc自动构建的类均存在这
│ │  ├─Services       //服务统一命名空间
│ │  └─GPBMetadata    //GRPC生成的元数据
├ └─protos            //所有proto存在这里
```



启动服务
./rr.exe serve

客户端 我这里用的是ApiPost 支持grpc测试  

    打开apipost客户端，新建grpc,选择导入proto  

    选择项目目录的 grpc/protos/base/hello-world.proto 文件

