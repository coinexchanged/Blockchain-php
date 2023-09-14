import websocket
import threading
import gzip
import json
import time
import _thread
import logging
import redis

local_data = threading.local()
r = redis.StrictRedis(host='127.0.0.1', port=6379)
logging.basicConfig(level=logging.WARNING,
                    filename='/tmp/python.log',
                    filemode='a',
                    format=
                    '%(asctime)s - %(pathname)s[line:%(lineno)d] - %(levelname)s: %(message)s'
                    )

def on_connect(ws):
    print(str(local_data.work_id) + '连接成功' + "\r\n")
    peroids = ['1min', '5min', '15min', '30min', '60min', '1day', '1week', '1mon','depth','detail']
    currency = ['btc', 'eth', 'xrp', 'ltc', 'eos', 'bch', 'etc', 'trb', 'iota', 'qtum', 'snt', 'wicc', 'neo','yee','doge']
    for index, item in enumerate(currency):
        if local_data.work_id == 9:
            key = "market." + currency[index] + "usdt.trade.detail"
        elif local_data.work_id == 8:
            key = "market." + currency[index] + "usdt.depth.step0"
        else:
            key = "market." + currency[index] + "usdt.kline." + peroids[local_data.work_id]
        ws.send(json.dumps({"sub": key, "id": key}))
        time.sleep(0.2)


def on_error(ws):
    print("出错" + str(local_data.work_id) + "\r\n")
    logging.error(str(local_data.work_id) + "失去连接，尝试重连")
    socket(local_data.work_id)


def on_close(ws):
    print("失去连接" + str(local_data.work_id) + "\r\n")
    logging.error(str(local_data.work_id) + "失去连接，尝试重连")
    socket(local_data.work_id)


def on_message(ws, data):
    obj = (gzip.decompress(data).decode())
    obj = json.loads(obj)

#     print(gzip.decompress(data).decode())
    # print(obj.keys())
    if 'ping' in obj.keys():
        print(str(local_data.work_id)+'收到心跳')
        ws.send(json.dumps({"pong": obj['ping']}))
        print(str(local_data.work_id)+'回应心跳' + "\r\n")
    else:
        if 'ch' in obj.keys():
            json_str = json.dumps(obj)
            json_str = json_str.replace("yee","hkcc")
            r.set(obj['ch'].replace('yee','hkcc'), json_str)


def on_data(obj):
    obj = obj


def socket(id):
    local_data.work_id = id
    websocket.enableTrace(False)
    ws = websocket.WebSocketApp("wss://api.huobi.pro/ws",
                                on_message=on_message,
                                on_error=on_error,
                                on_close=on_close)
    local_data.ws = ws
    ws.on_open = on_connect
    ws.run_forever()


currency = ['btc', 'eth', 'xrp', 'ltc', 'eos', 'bch', 'etc', 'trb', 'iota', 'qtum', 'snt', 'wicc', 'neo','yee','doge']
pero_ids = ['1min', '5min', '15min', '30min', '60min', '1day', '1week', '1mon','depth','detail']

for (index, value) in enumerate(pero_ids):
    _thread.start_new_thread(socket, (index,))

while 1:
    pass
