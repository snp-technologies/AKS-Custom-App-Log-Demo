# Azure Kubernetes Service Custom Application Log Demo

This ia a sample php application with Dockerfile and Kubernetes IaC to demonstrate Azure Monitor for containers in AKS for custom application logs.

Azure Monitor for containers collects stdout and stderr from container workloads deployed to AKS (or ACI). 

Knowing this, for custom application logging one just needs to route custom log information to stderr (or stdout) to take advantage of Azure Monitor for containers.

In this repo, we  containerize a simple php application and Rsyslog -  the **r**ocket-fast **sys**tem for **log** processing - to demonstrate application log integration with Azure Monitor for containers.

<a id="prerequisites"></a>
## Prerequisites
* [AKS cluster](https://docs.microsoft.com/en-us/azure/aks/tutorial-kubernetes-deploy-cluster)
* [Azure Container Registry](https://docs.microsoft.com/en-us/azure/container-registry/container-registry-get-started-azure-cli) or registry of your choice

<a id="implementation"></a>
## Implementation

### Step 1 - Build and push your Docker image

Ensure that your [Prerequisites](#prerequisites) are in place.

Clone this repo to your local dev environment. 

Edit the the following section of the `Dockerfile` to point to the source for your Drupal code:
```DOCKER
# Copy drupal code
WORKDIR /var/www/html
COPY . .
```
Build a container image:
```shell
docker build -t applogdemo .
```
Test it locally:
```shell
docker run --name applogdemo --rm -i -t applogdemo
```
The log output of you local test result should look similar to:
```shell
[ ok ] Starting enhanced syslogd: rsyslogd.
AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 172.17.0.2. Set the 'ServerName' directive globally to suppress this message
AH00558: apache2: Could not reliably determine the server's fully qualified domain name, using 172.17.0.2. Set the 'ServerName' directive globally to suppress this message
[Sat Jun 20 20:34:48.662731 2020] [mpm_prefork:notice] [pid 40] AH00163: Apache/2.4.25 (Debian) PHP/7.3.13 configured -- resuming normal operations
[Sat Jun 20 20:34:48.662809 2020] [core:notice] [pid 40] AH00094: Command line: '/usr/sbin/apache2 -D FOREGROUND'
```
While the container is running locally, in your browser enter some URL's to invoke the log handler, such as: 
http://localhost/?value=0, http://localhost/?value=2, etc.

You should see additional log output similar to:
```shell
myApp[41]: ERROR: at 2020/06/29 14:18:15
myApp[41]: Division by zero.
myApp[41]: 172.17.0.1
172.17.0.2:80 172.17.0.1 - - [29/Jun/2020:14:18:15 +0000] "GET /?value=0 HTTP/1.1" 200 366 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:7
7.0) Gecko/20100101 Firefox/77.0"
"-" - - [29/Jun/2020:14:18:15 +0000] "GET /?value=0 HTTP/1.1" 200 366 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 F
irefox/77.0"
myApp[42]: INFORMATION: at 2020/06/29 14:18:30
myApp[42]: Inverse of value succeeded
myApp[42]: 172.17.0.1
172.17.0.2:80 172.17.0.1 - - [29/Jun/2020:14:18:30 +0000] "GET /?value=4 HTTP/1.1" 200 268 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:7
7.0) Gecko/20100101 Firefox/77.0"
"-" - - [29/Jun/2020:14:18:30 +0000] "GET /?value=4 HTTP/1.1" 200 268 "-" "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0"
```
Once you have validated the application locally, prep the image for publishing to Azure Container Registry (Docker Hub or other registry is fine). Let's tag and push the image:
```shell
docker tag <image-name> <registry>/<image-name>:<tag>
docker push <registry>/<image-name>:<tag>
```
For example:
```shell
docker tag applogdemo myacr.azurecr.io/applogdemo:v1
docker push myacr.azurecr.io/applogdemo:v1
```

### Step 2 - Deploy to AKS

1. Customize the `image:` value in the `containers:` spec of the `deployment.yml` manifest, e.g.
    ```YAML
    containers:
        -
          image: myacr.azurecr.io/applogdemo:v1
    ```

4. Deploy the kubernetes manifests using the commands:
   ```
   kubectl apply -f manifests/deployment.yml
   ```

### Step 3 - Validate the deployment
Validate the deployment by accessing the website via the IP Address exposed by the Kubernetes LoadBalancer service. To identify the IP Address, use the command:
```
$ kubectl get svc drupal-service
NAME             TYPE           CLUSTER-IP   EXTERNAL-IP      PORT(S)        AGE
drupal-service   LoadBalancer   10.2.0.50    52.151.xxx.xxx   80:32758/TCP   10d
```
Test the application just as done for local testing.
Once you have called a sequence of URL's with the `?value=<some integer>` query string go to the Azure Monitor for containers blade in Azure Portal to view your custom application logs.

## In Closing

In this demo we cover the basic configurations necessary to enable custom application logging for Azure Monitor for containers.

From a log analysis standpoint, we've only scratched the  surface. Explore the Kusto query language to craft a wide variety of reports. Create alerts based on log metrics. Pin and share custom log dashboards.

Please feel free to share your questions and comments in the repo issue queue. Thanks - Mike :smiley:

## :books: Resources

- [Azure Monitor for containers overview](https://docs.microsoft.com/en-us/azure/azure-monitor/insights/container-insights-overview)
- [Getting started with Kusto](https://docs.microsoft.com/en-us/azure/data-explorer/kusto/concepts/)
- [Quickstart: Deploy an Azure Kubernetes Service cluster using the Azure CLI](https://docs.microsoft.com/en-us/azure/aks/kubernetes-walkthrough)
- [Quickstart: Deploy a container instance in Azure using the Azure CLI](https://docs.microsoft.com/en-us/azure/container-instances/container-instances-quickstart)
- [Rsyslog](https://github.com/rsyslog/rsyslog)
- [PHP syslog function](https://www.php.net/manual/en/function.syslog.php)
- [Log rotation with rsyslog](https://www.rsyslog.com/doc/v8-stable/tutorials/log_rotation_fix_size.html)
- [Docker image code for php:7.3-apache-stretch](https://github.com/docker-library/php/blob/master/7.3/stretch/apache/Dockerfile)

Git repository sponsored by [SNP Technologies](https://www.snp.com)