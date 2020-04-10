<template name="basics">
	<view>
		<cu-custom bgColor="bg-gradual-blue"> 
			<block slot="content">找会场</block>
		</cu-custom>
		<scroll-view scroll-y class="page">
			<img src="../../images/logo.fw.png" class="logo" alt="">
			<form class="form">
				<view class="cu-form-group margin-top">
					<view class="title">区域</view>
					<picker @change="areaChange" :value="i" :range="areas">
						<view class="picker">
							{{area>0?areas[i]:'全部'}}
						</view>
					</picker>
				</view>
				<view class="cu-form-group">
					<view class="title">星级</view>
					<picker @change="starChange" :value="i" :range="stars">
						<view class="picker">
							{{star>0?stars[i]:'全部'}}
						</view>
					</picker>
				</view>
				<view class="cu-form-group">
					<view class="title">价格</view>
					<picker @change="priceChange" :value="i" :range="prices">
						<view class="picker">
							{{price>0?prices[i]:'全部'}}
						</view>
					</picker>
				</view>
				<view class="cu-form-group">
					<view class="title">面积</view>
					<picker @change="mjChange" :value="i" :range="mjs">
						<view class="picker">
							{{mj>0?mjs[i]:'全部面积'}}
						</view>
					</picker>
				</view>
				<view class="cu-form-group">
					<view class="title">人数</view>
					<picker @change="peopleChange" :value="i" :range="peoples">
						<view class="picker">
							{{people>0?peoples[i]:'全部'}}
						</view>
					</picker>
				</view>
				<view class="cu-form-group">
					<view class="title">类别</view>
					
				</view>
				<view class="padding flex flex-direction"> 
					<button class="cu-btn bg-red margin-tb-sm lg">提交</button>
				</view>
				<navigator url="/pages/index/list">全部</navigator>
			</form>
			<view class="cu-tabbar-height"></view>
		</scroll-view>
	</view>
</template> 

<script>
	export default {
		data() {
			return {
				i:0,
				area: 0,
				star:0,
				price:0,
				people:0,
				mj:0,
				stars: ['一星', '二星', '三星以上'],
				areas: ['广东省', '广州市', '海珠区'],
				mjs:['80以下','80-160','160-200','200以上'],
				prices:['2000以下','2001-4000','4001-6000','6000以上'],
				peoples:['30人以下','30-60人','60-120人','120人以上']
			};
		},
		methods: {
			areaChange(e) {
				this.area = e.detail.value
			},
			starChange(e) {
				this.star = e.detail.value
			},
			priceChange(e) {
				this.price = e.detail.value
			},
			peopleChange(e) {
				this.people = e.detail.value
			},
			mjChange(e) {
				this.mj = e.detail.value
			},
			SwitchA(e) {
				this.switchA = e.detail.value
			},
			CheckboxChange(e) {
				var items = this.checkbox,
					values = e.detail.value;
				for (var i = 0, lenI = items.length; i < lenI; ++i) {
					items[i].checked = false;
					for (var j = 0, lenJ = values.length; j < lenJ; ++j) {
						if (items[i].value == values[j]) {
							items[i].checked = true;
							break
						}
					}
				}
			}
		}
	}
</script>

<style>
	.logo{
		width: 200px;margin: 20px auto;
	}
	.form{
		width: 90%;margin: 0 auto;
	}
	.cu-form-group .title {
		min-width: calc(4em + 15px);
	}
</style>
