## 通过php实现grpc服务端
php版本：
php >= 8.1

## 安装
创建项目
```
composer create-project zhonghaibin/grpc-service
```
在安装依赖包的时候，因为有墙等特殊网络原因（懂得都懂），有可能在获取rr二进制可执行文件会超时失败,重新手动获取 rr 可执行文件,在项目根目录打开命令行执行如下命令，我当初就是一直下载不了，导致我构建了个docker镜像。实在是下载不下来rr可执行文件。建议使用下面的Docker 安装和部署，简单又高效。
```
./vendor/bin/rr get-binary
```

启动服务(win10)
```
./rr.exe serve
```

## Docker 安装和部署

个人比较推荐使用Dockerfile构建容器进行部署.

创建项目
```
composer require zhonghaibin/grpc-service
```
进入目录
```
cd grpc-service
```
构建镜像
```
sudo  docker build -t php-grpc-service:latest .
```
创建容器
```
 sudo  docker run -d --name=grpc-serve -p 8000:8000 php-grpc-service:latest 
```
创建一次性容器
```
 sudo  docker run  --rm  -it --name=grpc-serve -p 8000:8000 php-grpc-service:latest sh
```
进入容器
```
 sudo  docker exec -it grpc-serve  sh
```
启动容器
```
sudo  docker start grpc-serve
```
停止容器
```
sudo  docker stop grpc-serve
```
删除容器
```
sudo  docker rm grpc-serve
```

删除镜像
```
sudo  docker rmi php-grpc-service:latest 
```
## docker-compose 部署
构建服务
```
docker-compose up
```
停止
```
docker-compose stop
```
启动
```
docker-compose start
```
其他命令 自行研究

## 客户端 
我这里用的是ApiPost 支持grpc测试

    打开apipost客户端，新建grpc,选择导入proto
    选择项目目录的 grpc/protos/base/hello-world.proto 文件


## 参考文档：

https://roadrunner.dev/docs/plugins-grpc/2023.x/en

1 根据自己的需求创建 hello-world.proto  文件

2 通过 hello-world.proto 生成 php 类文件
```
    这里需要用到下载两个可执行文件编译，我这里用的是win10：
    protoc.exe 和 protoc-gen-php-grpc.exe 
    下载地址：https://github.com/roadrunner-server/roadrunner/releases
    命令行执行如下命令：
    ./protoc.exe --plugin=protoc-gen-php-grpc.exe --php_out=./ --php-grpc_out=./ hello-world.proto
    会在当前目录下生成两个文件夹：GPBMetadata 和 Services
    把生成好的文件夹复制到 项目目录 grpc/generated 下
```
### 目录结构
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

### roadrunner-docker 参考文档
http://github.xiaoc.cn/roadrunner-server/roadrunner
