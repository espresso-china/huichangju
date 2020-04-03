var barrageWall = {
    container: null,
    barrageLen: null,
    lastElem: 0,
    overlapElem: -1,
    barrage: [],
    barrageOffset: [],
    init: function (option) {
        if (option.container === undefined) {
            console.error("必须指定 container 属性，container为弹幕容器的选择器");
            return false;
        }
        if (option.barrageLen === undefined) {
            console.error("必须指定 barrageLen 属性，barrageLen为弹幕轨道的数");
            return false;
        }
        this.container = option.container;
        this.barrageLen = option.barrageLen;
        for (var i = 0; i < this.barrageLen; i++) {
            this.barrage[i] = new Array();
        }
    },
    upWall: function (img, user, txt) {
        if (!this.barrageLen && this.container) {
            console.error("未检测到container和barrageLen属性，请先初始化弹幕墙并指定container和barrageLen属性");
            return false;
        }
        this.positionWall();
        var elem = $('<div></div>').addClass('list').css("top", this.lastElem * 38 + "px").html("<img src='" + img + "' alt=''/>" + user + "：" + txt).appendTo(this.container);
        this.barrage[this.lastElem].push(elem);
        setTimeout(function () {
            elem.addClass("animate");
        }, 200);
        setTimeout(function () {
            for (var i = 0; i < this.barrage.length; i++) {
                for (var x = 0; x < this.barrage[i].length; x++) {
                    if (this.barrage[i][x] === elem) {
                        this.barrage[i].splice(x, 1);
                        break;
                    }
                }
            }
            elem.remove();
        }.bind(this), 25000);
    },
    positionWall: function () {
        for (var i = 0; i <= this.barrage.length; i++) {
            if (i === this.barrage.length) {
                this.minOffset();
            } else {
                if (this.afterOffset(i)) break;
            }
        }
    },
    minOffset: function () {
        var minOffset = 0;
        for (var x = 0; x < this.barrage.length; x++) {
            var elem = this.barrage[x][this.barrage[x].length - 1];
            var aboveWidth = elem.width();
            var matrix = elem.css('transform');
            this.barrageOffset[x] = matrix === "none" ? -aboveWidth : -parseInt(matrix.split(",")[4]) - aboveWidth;
            minOffset = this.barrageOffset[x] > this.barrageOffset[minOffset] ? x : minOffset;
        }
        this.lastElem = minOffset;
    },
    afterOffset: function (i) {
        if (this.barrage[i].length === 0) {
            this.lastElem = i;
            this.overlapElem = -1;
            return true;
        } else {
            var elem = this.barrage[i][this.barrage[i].length - 1];
            var aboveWidth = elem.width();
            var matrix = elem.css('transform');
            if (matrix !== "none") {
                var aboveTransform = parseInt(matrix.split(",")[4]);
                if (-aboveTransform - aboveWidth > 50) {
                    this.lastElem = i;
                    this.overlapElem = -1;
                    return true;
                }
            }
        }
        return false;
    }
}
