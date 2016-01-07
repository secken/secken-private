# 洋葱企业内网验证系统服务端API
最后更新时间（中文版）:01/06/2016

## 介绍（Introduce）
洋葱企业内网验证系统服务端API是搭配洋葱企业内网验证系统使用的，在安装好内网系统之后，便能够启用，是桥接内网业务系统和洋葱内网验证系统主要组成。
此套接口提供主要提供了获取二维码、查询验证结果、发起推送验证等功能，开发者可通过这些接口实现内网业务系统和洋葱内网验证系统的对接，实现内网业务系统的扫描登录、推送验证等功能。

## 接口（Fuction List）

### 获取二维码内容并发起验证事件（Get YangAuth QrCode）

获取二维码内容并发起验证事件，通过调用此接口，您将得到用于扫码登录或者扫码绑定界面所需的二维码地址（QrCodeUrl），同时得到后续用来查询验证结果的事件ID（EventId）, 如需定制二维码样式，您可以通过二维码内容（QrCodeData）字段的数据自己生成特定样式的二维码。
PS：由于此接口提供的二维码是有时效性的，建议您在前端界面每隔60秒重新获取一次。

请求方式：
```
POST http://iam.domain.com/api/access/qrcode_for_auth HTTP/1.1
Accept: application/json
```
请求参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----:|:----:|:---|
| power_id | string | 是 | 洋葱内网系统中的权限ID，用来标识每个请求方身份的唯一标识 |
| signature | string | 是 | 40位的SHA1摘要签名，确保请求方提交数据完整性 |

请求示例：
```
{
  "power_id": "ubfjVKuV7HHKuGFYwyHG",
  "signature": "01bc1fc5e821504c8a2e47575514af75ef8d274d"
}
```
返回参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----|:----:|:---|
| status  | int | 是 | 每次请求的返回状态码 |
| description | string | 是 | 每次请求的返回状态说明 |
| event_id | string | 否 | 每次扫码或者推送事件的唯一标识 |
| qrcode_url | string | 否 | 二维码的请求地址，直接可请求展示 |
| qrcode_data | string | 否 | 二维码图片的内容，方便自定义二维码样式 |
| signature | string | 否 | 40位的SHA1摘要签名，确保服务方返回数据完整性 |

返回示例：
```
{
    "description": "请求成功",
    "event_id": "1452070406.05BPk4qmD",
    "qrcode_data": "http://yc.im/lkGbeWAXZ+HwbwZbUxttoo77aR2dcIUWAThPUm2dhHRGKX9Xzw25+VU/XgcSGidmy/api_cluster_1",
    "qrcode_url": "https://qrbj01.yangcong.com/3d5d21f0-d5d5-4284-a12e-6dafaf659ed2",
    "signature": "dcc8599424eb9656ef9b22aceea819a6",
    "status": 200
}
```

### 查询验证事件的结果（Check YangAuth Result）

查询验证事件的结果，通过调用此接口，您将查询到扫码或者推送等验证事件的结果，通常我们以返回状态码（Status）为主要是参考依据，当返回状态码为200时，意味着用户已经成功在客户端完成了相关的验证操作，并且洋葱内网验证系统通过该用户和权限的关联关系判定此用户具备此权限，此时内网业务系统可以授权其完成后续动作。

请求方式：
```
GET http://iam.domain.com/api/access/event_result HTTP/1.1
Accept: application/json
```
请求参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----:|:----:|:---|
| power_id | string | 是 | 洋葱内网系统中的权限ID，用来标识每个请求方身份的唯一标识 |
| event_id | string | 是 | 通过二维码或者推送接口得到的验证事件唯一标识 |
| signature | string | 是 | 40位的SHA1摘要签名，确保请求方提交数据完整性 |

请求示例：
```
{
  "power_id": "ubfjVKuV7HHKuGFYwyHG",
  "event_id": "1452076833.14zAY6Tfp",
  "signature": "fbaf4efa625b64a0be4ebb74e1c11db7496c24ff"
}
```
返回参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----|:----:|:---|
| status  | int | 是 | 每次请求的返回状态码 |
| description | string | 是 | 每次请求的返回状态说明 |
| event_id | string | 否 | 每次扫码或者推送事件的唯一标识 |
| uid | string | 否 | 用户唯一身份标识，可用于业务系统绑定、验证、发起推送等操作 |
| signature | string | 否 | 40位的SHA1摘要签名，确保服务方返回数据完整性 |

返回示例：

当用户还没完成验证操作的时候：
```
{
    "description": "no user agree,retry",
    "status": 602
}
```
当用户已经完成验证操作的时候：
```
{
    "description": "success",
    "event_id": "1452076833.14zAY6Tfp",
    "signature": "1da23935c302ebbd5fdf51ce00832b48",
    "status": 200,
    "uid": "xEJOkZ4TbjWHaoqGaXu2qw=="
}
```

### 发起推送验证事件（Ask YangAuth Push）
 
发起推送验证事件，通过调用此接口，您可以向指定用户发起一个推送验证请求，这个指定用户必须是内网验证系统中已存在的用户，并且至少在移动客户端使用该身份注册过一次，才可以确保其能顺利收到推送验证。

请求方式：
```
POST http://iam.domain.com/api/access/realtime_authorization HTTP/1.1
Accept: application/json
```
请求参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----:|:----:|:---|
| power_id | string | 是 | 洋葱内网系统中的权限ID，用来标识每个请求方身份的唯一标识 |
| username | string | 是 | 用户唯一身份标识，必须是内网验证系统中存在的用户标识 |
| signature | string | 是 | 40位的SHA1摘要签名，确保请求方提交数据完整性 |

请求示例：
```
{
  "power_id": "ubfjVKuV7HHKuGFYwyHG",
  "username": "zhangsan",
  "signature": "b98ee1ac77dc2f74bf6c81297c9e74d6f58a90fc"
}
```
返回参数：

| 参数名 | 类型 | 必选 | 用途 |
|:-----|:----|:----:|:---|
| status  | int | 是 | 每次请求的返回状态码 |
| description | string | 是 | 每次请求的返回状态说明 |
| event_id | string | 否 | 每次扫码或者推送事件的唯一标识 |
| signature | string | 否 | 40位的SHA1摘要签名，确保服务方返回数据完整性 |

返回示例：
```
{
    "description": "请求成功",
    "event_id": "1452077771.25DGVTB93",
    "signature": "82f225fc641c6e5f4fe5c2759d4fcfff",
    "status": 200
}
```
## 签名规则（Signature Rules）

### 签名基本介绍:
洋葱内网验证系统API为了确保请求方提交和服务方返回数据的完整性，针对上行和下行数据都采取了签名机制。
所有请求或者返回参数根据Key名按字典排序，依次拼接成签名原始串，再进行SHA1摘要。

### 签名基本格式:
```
SHA1($ParamKey1=$ParamValue1$ParamKey2=$ParamValue2...$PowerKey)
```
### 签名基本示例：

基本示例授权：
```
{
  "powerHost": "http://iam.domain.com",
  "powerId": "ubfjVKuV7HHKuGFYwyHG",
  "powerKey": "Q0eYeCju5wg9qSXHvEkkdSwhnqoHvaRO"
}
```
获取二维码内容并发起验证事件（Get YangAuth QrCode）
```
Signature = SHA1("{0}={1}{2}","power_id",$PowerId,$PowerKey)
Signature = SHA1("{0}={1}{2}","power_id","ubfjVKuV7HHKuGFYwyHG","Q0eYeCju5wg9qSXHvEkkdSwhnqoHvaRO")
Signature = "01bc1fc5e821504c8a2e47575514af75ef8d274d"
```
查询验证事件的结果（Check YangAuth Result）
```
Signature = SHA1("{0}={1}{2}={3}{4}","event_id",$EventId,"power_id",$PowerId,$PowerKey)
Signature = SHA1("{0}={1}{2}={3}{4}","event_id","1452076833.14zAY6Tfp","power_id","ubfjVKuV7HHKuGFYwyHG","Q0eYeCju5wg9qSXHvEkkdSwhnqoHvaRO")
Signature = "fbaf4efa625b64a0be4ebb74e1c11db7496c24ff"
```
发起推送验证事件（Ask YangAuth Push）
```
Signature = SHA1("{0}={1}{2}={3}{4}","power_id",$PowerId,"username",$UserName,$PowerKey)
Signature = SHA1("{0}={1}{2}={3}{4}","power_id","ubfjVKuV7HHKuGFYwyHG","username","zhangsan","Q0eYeCju5wg9qSXHvEkkdSwhnqoHvaRO")
Signature = "b98ee1ac77dc2f74bf6c81297c9e74d6f58a90fc"
```

## 状态码列表（Status Code List）

| 状态码 | 说明 |
|:-----:|:----|
| 200  |  请求成功或者验证成功 |
| 201  |  二维码已被扫描 |
| 400  |  请求参数格式 |
| 401  |  App 状态错误 |
| 402  |  App或Id错误 |
| 403  |  签名错误 |
| 404  |  请求api不存在 |
| 405  |  请求方法错误 |
| 406  |  不在应用白名单内 |
| 500  |  洋葱系统服务错误 |
| 501  |  生成二维码图片失败 |
| 600  |  动态验证码错误 |
| 601  |  用于拒绝授权 |
| 602  |  等待用户响应超时，可重试 |
| 603  |  等待用户响应超时，不可重试 |
| 604  |  事件ID不存在 |
| 605  |  用户未开启该验证类型 |
| 607  |  用户不存在 |
| 900  |  用户没有访问该资源的权限 |



