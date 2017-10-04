var ws, name, client_list={},chess_list={},img,ctx,canvas;
canvas = document.getElementById('canvas');
var isWhite = true; //设置是否该轮到白棋，黑棋先手
var winner = ''; //赢家初始化为空
var step=225;//总步数
var chessData = new Array(15); //二维数组存储棋盘落子信息,初始化数组chessData值为0即此处没有棋子，1为白棋，2为黑棋
for (var x = 0; x < 15; x++) {
    chessData[x] = new Array(15);
    for (var y = 0; y < 15; y++) {
        chessData[x][y] = 0;
    }
}
ws = new WebSocket("ws://"+document.domain+":7272");
//js入口
function onLoad_d(){
    connect();
    loadChessMap();
}
function loadChessMap() {
    //用图片做棋盘在放缩后图片会失真
    img = new Image();
    img.src = 'image/map.png';
    canvas = document.getElementById('canvas');
    ctx = canvas.getContext('2d');
    img.onload = function() {
        ctx.drawImage(img, 0, 0);
        img.style.display = 'none';
    };
    canvas.addEventListener('click',onclick);
    onclick();
}
//将窗口坐标转成canvas坐标
function windowTocanvas(canvas, x, y) {
    var bbox = canvas.getBoundingClientRect();
    return {
        x: x - bbox.left * (canvas.width / bbox.width),
        y: y - bbox.top * (canvas.height / bbox.height)
    };
}
//没有对重复落子进行检查
function onclick(event) {
    var e = event ? event : window.event;
    var loc=windowTocanvas(canvas,e.clientX,e.clientY);
    var px=parseInt(loc.x);
    var py=parseInt(loc.y);
    px=px-44;
    py=py-40;
    var x;
    var y;
    //四舍五入取正增大点击面积
    if(x!=0){x=Math.round(px/49);}
    if(y!=0){y=Math.round(py/48);}
    console.log(x);
    console.log(y);
    // if (px < 0 || py < 0 || x > 14 || y > 14 || chessData[x][y] != 0) { //鼠标点击棋盘外的区域不响应
    //     return;
    // }
    dochess(x,y);
}
//加入监听
//canvas.addEventListener('click', click);
function chess(color, x, y) {
    ctx.fillStyle = color; //绘制棋子
    ctx.beginPath();
    ctx.arc(x * 50 + 48, y * 50 + 40, 15, 0, Math.PI * 2, true);
    ctx.closePath();
    ctx.fill();
    if (color == "white") {
        console.log("电脑在" + x + "," + y + "画了个白棋");
        chessData[x][y] = 1;
    } else {
        console.log("电脑在" + x + "," + y + "画了个黑棋");
        chessData[x][y] = 2;
    }
}
function dochess(x,y) {
    if(isNaN(x)||isNaN(y))
    {
        return;
    }
    if(chessData[x][y]==0){
        if(isWhite ){
            chess("white",x,y);
            //x*100+y
            var date=x*100+y;
            ws.send('{"type":"update","data":"'+date+'"}');
            console.log()
            isWhite=false;
        }else {
            chess("black",x,y);
            var date=x*100+y;
            ws.send('{"type":"update","data":"'+date+'"}');
            isWhite=true;
        }
    }else {
      console.log("当前位置存在棋子");
    }
}
// 连接服务端
function connect() {
    // 创建websocket
    ws = new WebSocket("ws://"+document.domain+":7272");
    // 当socket连接打开时，输入用户名
    ws.onopen = onopen;
    // 当有消息时根据消息类型显示不同信息
    ws.onmessage = onmessage;
    ws.onclose = function() {
        console.log("连接关闭，定时重连");
        connect();
    };
    ws.onerror = function() {
        console.log("出现错误");
    };
}

// 连接建立时发送登录信息
function onopen() {
    if (!name) {
        name = prompt('输入你的名字：', '');
        if(!name || name=='null'){
            name = '游客';
        }
    }
    // 登录
    var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '"}';
    console.log("websocket握手成功，发送登录数据:" + login_data);
    ws.send(login_data);

}

// 服务端发来消息时//进行下子和数据传输
function onmessage(e)
{
    console.log(e.data);
    var data = eval("("+e.data+")");
    switch(data['type']){
        // 服务端ping客户端
        case 'ping':
            ws.send('{"type":"pong"}');
            break;
            //更新对方棋子位置
            //数据保存格式是1000 前两位x 后两位y 10*100=1000; 7+1000=1007;

        case 'update':
            if(!data['update']){
                console.log("服务器没有返回x");
                ws.send('{"type":"request_again"}');
                return;
            }else {
                var da =data['update'];
                cx=parseInt(da/100);
                cy=da-(cx*100);
                dochess(cx,cy);
            }
            break;

    }
}




