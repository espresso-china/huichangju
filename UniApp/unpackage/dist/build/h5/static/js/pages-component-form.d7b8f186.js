(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-component-form"],{"10ad":function(t,i,e){"use strict";e.r(i);var a=e("9520"),s=e.n(a);for(var c in a)"default"!==c&&function(t){e.d(i,t,(function(){return a[t]}))}(c);i["default"]=s.a},9520:function(t,i,e){"use strict";Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={data:function(){return{index:-1,picker:["喵喵喵","汪汪汪","哼唧哼唧"],multiArray:[["无脊柱动物","脊柱动物"],["扁性动物","线形动物","环节动物","软体动物","节肢动物"],["猪肉绦虫","吸血虫"]],objectMultiArray:[[{id:0,name:"无脊柱动物"},{id:1,name:"脊柱动物"}],[{id:0,name:"扁性动物"},{id:1,name:"线形动物"},{id:2,name:"环节动物"},{id:3,name:"软体动物"},{id:3,name:"节肢动物"}],[{id:0,name:"猪肉绦虫"},{id:1,name:"吸血虫"}]],multiIndex:[0,0,0],time:"12:01",date:"2018-12-25",region:["广东省","广州市","海珠区"],switchA:!1,switchB:!0,switchC:!1,switchD:!1,radio:"A",checkbox:[{value:"A",checked:!0},{value:"B",checked:!0},{value:"C",checked:!1}],imgList:[],modalName:null,textareaAValue:"",textareaBValue:""}},methods:{PickerChange:function(t){this.index=t.detail.value},MultiChange:function(t){this.multiIndex=t.detail.value},MultiColumnChange:function(t){var i={multiArray:this.multiArray,multiIndex:this.multiIndex};switch(i.multiIndex[t.detail.column]=t.detail.value,t.detail.column){case 0:switch(i.multiIndex[0]){case 0:i.multiArray[1]=["扁性动物","线形动物","环节动物","软体动物","节肢动物"],i.multiArray[2]=["猪肉绦虫","吸血虫"];break;case 1:i.multiArray[1]=["鱼","两栖动物","爬行动物"],i.multiArray[2]=["鲫鱼","带鱼"];break}i.multiIndex[1]=0,i.multiIndex[2]=0;break;case 1:switch(i.multiIndex[0]){case 0:switch(i.multiIndex[1]){case 0:i.multiArray[2]=["猪肉绦虫","吸血虫"];break;case 1:i.multiArray[2]=["蛔虫"];break;case 2:i.multiArray[2]=["蚂蚁","蚂蟥"];break;case 3:i.multiArray[2]=["河蚌","蜗牛","蛞蝓"];break;case 4:i.multiArray[2]=["昆虫","甲壳动物","蛛形动物","多足动物"];break}break;case 1:switch(i.multiIndex[1]){case 0:i.multiArray[2]=["鲫鱼","带鱼"];break;case 1:i.multiArray[2]=["青蛙","娃娃鱼"];break;case 2:i.multiArray[2]=["蜥蜴","龟","壁虎"];break}break}i.multiIndex[2]=0;break}this.multiArray=i.multiArray,this.multiIndex=i.multiIndex},TimeChange:function(t){this.time=t.detail.value},DateChange:function(t){this.date=t.detail.value},RegionChange:function(t){this.region=t.detail.value},SwitchA:function(t){this.switchA=t.detail.value},SwitchB:function(t){this.switchB=t.detail.value},SwitchC:function(t){this.switchC=t.detail.value},SwitchD:function(t){this.switchD=t.detail.value},RadioChange:function(t){this.radio=t.detail.value},CheckboxChange:function(t){for(var i=this.checkbox,e=t.detail.value,a=0,s=i.length;a<s;++a){i[a].checked=!1;for(var c=0,n=e.length;c<n;++c)if(i[a].value==e[c]){i[a].checked=!0;break}}},ChooseImage:function(){var t=this;uni.chooseImage({count:4,sizeType:["original","compressed"],sourceType:["album"],success:function(i){0!=t.imgList.length?t.imgList=t.imgList.concat(i.tempFilePaths):t.imgList=i.tempFilePaths}})},ViewImage:function(t){uni.previewImage({urls:this.imgList,current:t.currentTarget.dataset.url})},DelImg:function(t){var i=this;uni.showModal({title:"召唤师",content:"确定要删除这段回忆吗？",cancelText:"再看看",confirmText:"再见",success:function(e){e.confirm&&i.imgList.splice(t.currentTarget.dataset.index,1)}})},textareaAInput:function(t){this.textareaAValue=t.detail.value},textareaBInput:function(t){this.textareaBValue=t.detail.value}}};i.default=a},a886:function(t,i,e){"use strict";e.r(i);var a=e("fa62"),s=e("10ad");for(var c in s)"default"!==c&&function(t){e.d(i,t,(function(){return s[t]}))}(c);e("fd50");var n,u=e("f0c5"),l=Object(u["a"])(s["default"],a["b"],a["c"],!1,null,"7a17c2ce",null,!1,a["a"],n);i["default"]=l.exports},b5e9:function(t,i,e){var a=e("e963");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var s=e("4f06").default;s("4843dc72",a,!0,{sourceMap:!1,shadowMode:!1})},e963:function(t,i,e){var a=e("24fb");i=a(!1),i.push([t.i,".cu-form-group .title[data-v-7a17c2ce]{min-width:calc(4em + 15px)}",""]),t.exports=i},fa62:function(t,i,e){"use strict";var a,s=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("v-uni-view",[e("cu-custom",{attrs:{bgColor:"bg-gradual-pink",isBack:!0}},[e("template",{attrs:{slot:"backText"},slot:"backText"},[t._v("返回")]),e("template",{attrs:{slot:"content"},slot:"content"},[t._v("表单")])],2),e("v-uni-form",[e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("邮件")]),e("v-uni-input",{attrs:{placeholder:"两字短标题",name:"input"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("输入框")]),e("v-uni-input",{attrs:{placeholder:"三字标题",name:"input"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("收货地址")]),e("v-uni-input",{attrs:{placeholder:"统一标题的宽度",name:"input"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("收货地址")]),e("v-uni-input",{attrs:{placeholder:"输入框带个图标",name:"input"}}),e("v-uni-text",{staticClass:"cuIcon-locationfill text-orange"})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("验证码")]),e("v-uni-input",{attrs:{placeholder:"输入框带个按钮",name:"input"}}),e("v-uni-button",{staticClass:"cu-btn bg-green shadow"},[t._v("验证码")])],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("手机号码")]),e("v-uni-input",{attrs:{placeholder:"输入框带标签",name:"input"}}),e("v-uni-view",{staticClass:"cu-capsule radius"},[e("v-uni-view",{staticClass:"cu-tag bg-blue "},[t._v("+86")]),e("v-uni-view",{staticClass:"cu-tag line-blue"},[t._v("中国大陆")])],1)],1),e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("普通选择")]),e("v-uni-picker",{attrs:{value:t.index,range:t.picker},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.PickerChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"picker"},[t._v(t._s(t.index>-1?t.picker[t.index]:"禁止换行，超出容器部分会以 ... 方式截断"))])],1)],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("多列选择")]),e("v-uni-picker",{attrs:{mode:"multiSelector",value:t.multiIndex,range:t.multiArray},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.MultiChange.apply(void 0,arguments)},columnchange:function(i){arguments[0]=i=t.$handleEvent(i),t.MultiColumnChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"picker"},[t._v(t._s(t.multiArray[0][t.multiIndex[0]])+"，"+t._s(t.multiArray[1][t.multiIndex[1]])+"，"+t._s(t.multiArray[2][t.multiIndex[2]]))])],1)],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("时间选择")]),e("v-uni-picker",{attrs:{mode:"time",value:t.time,start:"09:01",end:"21:01"},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.TimeChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"picker"},[t._v(t._s(t.time))])],1)],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("日期选择")]),e("v-uni-picker",{attrs:{mode:"date",value:t.date,start:"2015-09-01",end:"2020-09-01"},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.DateChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"picker"},[t._v(t._s(t.date))])],1)],1),e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("开关选择")]),e("v-uni-switch",{class:t.switchA?"checked":"",attrs:{checked:!!t.switchA},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.SwitchA.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义颜色")]),e("v-uni-switch",{staticClass:"red",class:t.switchB?"checked":"",attrs:{checked:!!t.switchB},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.SwitchB.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义图标")]),e("v-uni-switch",{staticClass:"switch-sex",class:t.switchC?"checked":"",attrs:{checked:!!t.switchC},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.SwitchC.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("方形开关")]),e("v-uni-switch",{staticClass:"orange radius",class:t.switchD?"checked":"",attrs:{checked:!!t.switchD},on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.SwitchD.apply(void 0,arguments)}}})],1),e("v-uni-radio-group",{staticClass:"block",on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.RadioChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("单选操作(radio)")]),e("v-uni-radio",{class:"A"==t.radio?"checked":"",attrs:{checked:"A"==t.radio,value:"A"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义样式")]),e("v-uni-radio",{staticClass:"radio",class:"B"==t.radio?"checked":"",attrs:{checked:"B"==t.radio,value:"B"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义颜色")]),e("v-uni-view",[e("v-uni-radio",{staticClass:"blue radio",class:"C"==t.radio?"checked":"",attrs:{checked:"C"==t.radio,value:"C"}}),e("v-uni-radio",{staticClass:"red margin-left-sm",class:"D"==t.radio?"checked":"",attrs:{checked:"D"==t.radio,value:"D"}})],1)],1)],1),e("v-uni-checkbox-group",{staticClass:"block",on:{change:function(i){arguments[0]=i=t.$handleEvent(i),t.CheckboxChange.apply(void 0,arguments)}}},[e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("复选选操作(checkbox)")]),e("v-uni-checkbox",{class:t.checkbox[0].checked?"checked":"",attrs:{checked:!!t.checkbox[0].checked,value:"A"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义形状")]),e("v-uni-checkbox",{staticClass:"round",class:t.checkbox[1].checked?"checked":"",attrs:{checked:!!t.checkbox[1].checked,value:"B"}})],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"title"},[t._v("定义颜色")]),e("v-uni-checkbox",{staticClass:"round blue",class:t.checkbox[2].checked?"checked":"",attrs:{checked:!!t.checkbox[2].checked,value:"C"}})],1)],1),e("v-uni-view",{staticClass:"cu-bar bg-white margin-top"},[e("v-uni-view",{staticClass:"action"},[t._v("图片上传")]),e("v-uni-view",{staticClass:"action"},[t._v(t._s(t.imgList.length)+"/4")])],1),e("v-uni-view",{staticClass:"cu-form-group"},[e("v-uni-view",{staticClass:"grid col-4 grid-square flex-sub"},[t._l(t.imgList,(function(i,a){return e("v-uni-view",{key:a,staticClass:"bg-img",attrs:{"data-url":t.imgList[a]},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.ViewImage.apply(void 0,arguments)}}},[e("v-uni-image",{attrs:{src:t.imgList[a],mode:"aspectFill"}}),e("v-uni-view",{staticClass:"cu-tag bg-red",attrs:{"data-index":a},on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.DelImg.apply(void 0,arguments)}}},[e("v-uni-text",{staticClass:"cuIcon-close"})],1)],1)})),t.imgList.length<4?e("v-uni-view",{staticClass:"solids",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.ChooseImage.apply(void 0,arguments)}}},[e("v-uni-text",{staticClass:"cuIcon-cameraadd"})],1):t._e()],2)],1),e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-view",{staticClass:"title"},[t._v("头像")]),e("v-uni-view",{staticClass:"cu-avatar radius bg-gray"})],1),e("v-uni-view",{staticClass:"cu-form-group margin-top"},[e("v-uni-textarea",{attrs:{maxlength:"-1",disabled:null!=t.modalName,placeholder:"多行文本输入框"},on:{input:function(i){arguments[0]=i=t.$handleEvent(i),t.textareaAInput.apply(void 0,arguments)}}})],1),e("v-uni-view",{staticClass:"cu-form-group align-start"},[e("v-uni-view",{staticClass:"title"},[t._v("文本框")]),e("v-uni-textarea",{attrs:{maxlength:"-1",disabled:null!=t.modalName,placeholder:"多行文本输入框"},on:{input:function(i){arguments[0]=i=t.$handleEvent(i),t.textareaBInput.apply(void 0,arguments)}}})],1)],1)],1)},c=[];e.d(i,"b",(function(){return s})),e.d(i,"c",(function(){return c})),e.d(i,"a",(function(){return a}))},fd50:function(t,i,e){"use strict";var a=e("b5e9"),s=e.n(a);s.a}}]);