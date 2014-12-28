	var module = new Object;
	//"require:nomunge,exports:nomunge,module:nomunge";
	var gps = '';
	var emotion = '';
	
	
	module.exports = {
		maxUpload: 8,
		uploadInfo: {},
		uploadQueue: [],
		previewQueue: [],
		xhr: {},
		isBusy: false,
		getgps: 1,
		countUpload: function() {
			var num = 0;
			jq.each(module.exports.uploadInfo, function(i, n) {
				if (n) {
					++num
				}
			});
			return num
		},
		uploadPreview: function(id) {
			var reader = new FileReader();
			var uploadBase64;
			var conf = {},
				file = module.exports.uploadInfo[id].file;
			reader.onload = function(e) {
				var result = this.result;
				if (file.type == 'image/jpeg') {
					try {
						var jpg = new JpegMeta.JpegFile(result, file.name)
					} catch (e) {
						jq.DIC.dialog({
							content: '图片不是正确的图片数据',
							autoClose: true
						});
						jq('#li' + id).remove();
						return false
					}
					if (jpg.tiff && jpg.tiff.Orientation) {
						conf = jq.extend(conf, {
							orien: jpg.tiff.Orientation.value
						})
					}
				}
				if (ImageCompresser.support()) {
					var img = new Image();
					img.onload = function() {
						console.log(conf);
						try {
							uploadBase64 = ImageCompresser.getImageBase64(this, conf)
						} catch (e) {
							jq.DIC.dialog({
								content: '压缩图片失败',
								autoClose: true
							});
							jq('#li' + id).remove();
							return false
						}
						if (uploadBase64.indexOf('data:image') < 0) {
							jq.DIC.dialog({
								content: '上传照片格式不支持',
								autoClose: true
							});
							jq('#li' + id).remove();
							return false
						}
						module.exports.uploadInfo[id].file = uploadBase64;
						jq('#li' + id).find('img').attr('src', uploadBase64);
						module.exports.uploadQueue.push(id)
					}
					img.onerror = function() {
						jq.DIC.dialog({
							content: '解析图片数据失败',
							autoClose: true
						});
						jq('#li' + id).remove();
						return false
					}
					img.src = ImageCompresser.getFileObjectURL(file)
				} else {
					uploadBase64 = result;
					if (uploadBase64.indexOf('data:image') < 0) {
						jq.DIC.dialog({
							content: '上传照片格式不支持',
							autoClose: true
						});
						jq('#li' + id).remove();
						return false
					}
					module.exports.uploadInfo[id].file = uploadBase64;
					jq('#li' + id).find('img').attr('src', uploadBase64);
					module.exports.uploadQueue.push(id)
				}
			}
			reader.readAsBinaryString(module.exports.uploadInfo[id].file)
		},
		createUpload: function(id) {
			if (!module.exports.uploadInfo[id]) {
				return false
			}
			var uploadUrl = UploadUrl;
			var progressHtml = '<div class="progress" id="progress' + id + '"><div class="proBar" style="width:0%;"></div></div>';
			jq('#li' + id).find('.maskLay').after(progressHtml);
			var formData = new FormData();
			formData.append('pic', module.exports.uploadInfo[id].file);
			formData.append('CSRFToken', CSRFToken);
			formData.append('sId', sId);
			formData.append('id', id);
			var progress = function(e) {
					if (e.target.response) {
						var result = jq.parseJSON(e.target.response);
						if (result.errCode != 0) {
							jq.DIC.dialog({
								content: '网络不稳定，请稍后重新操作',
								autoClose: true
							});
							removePic(id)
						}
					}
					var progress = jq('#progress' + id).find('.proBar');
					if (e.total == e.loaded) {
						var percent = 100
					} else {
						var percent = 100 * (e.loaded / e.total)
					}
					if (percent > 100) {
						percent = 100
					}
					progress.css('width', percent + '%');
					if (percent == 100) {
						jq('#li' + id).find('.maskLay').remove();
						jq('#li' + id).find('.progress').remove()
					}
				}
			var removePic = function(id) {
					donePic(id);
					jq('#li' + id).remove()
				}
			var donePic = function(id) {
					module.exports.isBusy = false;
					if (typeof module.exports.uploadInfo[id] != 'undefined') {
						module.exports.uploadInfo[id].isDone = true
					}
					if (typeof module.exports.xhr[id] != 'undefined') {
						module.exports.xhr[id] = null
					}
				}
			var complete = function(e) {
					var progress = jq('#progress' + id).find('.proBar');
					progress.css('width', '100%');
					jq('#li' + id).find('.maskLay').remove();
					jq('#li' + id).find('.progress').remove();
					donePic(id);
					var result = jq.parseJSON(e.target.response);
					if (result.errCode == 0) {
						var input = '<input type="hidden" id="input' + result.data.id + '" name="picIds[]" value="' + result.data.picId + '">';
						jq('#newthread').append(input)
					} else {
						jq.DIC.dialog({
							content: '网络不稳定，请稍后重新操作',
							autoClose: true
						});
						removePic(id)
					}
				}
			var failed = function() {
					jq.DIC.dialog({
						content: '网络断开，请稍后重新操作',
						autoClose: true
					});
					removePic(id)
				}
			var abort = function() {
					jq.DIC.dialog({
						content: '上传已取消',
						autoClose: true
					});
					removePic(id)
				}
			module.exports.xhr[id] = new XMLHttpRequest();
			module.exports.xhr[id].addEventListener("progress", progress, false);
			module.exports.xhr[id].addEventListener("load", complete, false);
			module.exports.xhr[id].addEventListener("abort", abort, false);
			module.exports.xhr[id].addEventListener("error", failed, false);
			module.exports.xhr[id].open("POST", uploadUrl + '&t=' + Date.now());
			module.exports.xhr[id].send(formData)
		},
		initUpload: function() {
			jq('#uploadFile').on('click', function() {
				
				if (module.exports.isBusy) {
					jq.DIC.dialog({
						content: '上传中，请稍后添加',
						autoClose: true
					});
					return false
				}
			});
			jq('body').on('change', '#uploadFile', function(e) {
				e = e || window.event;
				var fileList = e.target.files;
				if (!fileList.length) {
					return false
				}
				for (var i = 0; i < fileList.length; i++) {
					if (module.exports.countUpload() >= module.exports.maxUpload) {
						jq.DIC.dialog({
							content: '你最多只能上传8张照片',
							autoClose: true
						});
						break
					}
					var file = fileList[i];
					if (!module.exports.checkPicSize(file)) {
						jq.DIC.dialog({
							content: '图片体积过大',
							autoClose: true
						});
						continue
					}
					if (!module.exports.checkPicType(file)) {
						jq.DIC.dialog({
							content: '上传照片格式不支持',
							autoClose: true
						});
						continue
					}
					var id = Date.now() + i;
					module.exports.uploadInfo[id] = {
						file: file,
						isDone: false,
					};
					var html = '<li id="li' + id + '"><div class="photoCut"><img src="http://dzqun.gtimg.cn/quan/images/defaultImg.png" class="attchImg" alt="photo"></div>' + '<div class="maskLay"></div>' + '<a href="javascript:;" class="cBtn spr db " title="" _id="' + id + '">关闭</a></li>';
					jq('#addPic').before(html);
					module.exports.previewQueue.push(id)
				}
				if (module.exports.countUpload() >= module.exports.maxUpload) {
					jq('#addPic').hide()
				}
				jq(this).val('')
			});
			jq('.photoList').on('click', '.cBtn', function() {
				var id = jq(this).attr('_id');
				if (module.exports.xhr[id]) {
					module.exports.xhr[id].abort()
				}
				jq('#li' + id).remove();
				jq('#input' + id).remove();
				module.exports.uploadInfo[id] = null;
				if (module.exports.countUpload() < module.exports.maxUpload) {
					jq('#addPic').show()
				}
			});
			setInterval(function() {
				setTimeout(function() {
					if (module.exports.previewQueue.length) {
						var jobId = module.exports.previewQueue.shift();
						module.exports.uploadPreview(jobId)
					}
				}, 1);
				setTimeout(function() {
					if (!module.exports.isBusy && module.exports.uploadQueue.length) {
						var jobId = module.exports.uploadQueue.shift();
						module.exports.isBusy = true;
						module.exports.createUpload(jobId)
					}
				}, 10)
			}, 300)
		},
		init: function() {
			var storageKey = sId + "thread_content";
			//jq('#content').val(localStorage.getItem(storageKey));
			timer = setInterval(function() {
				jq.DIC.strLenCalc(jq('textarea[name="content"]')[0], 'pText', 1000);
				localStorage.removeItem(storageKey);
				localStorage.setItem(storageKey, jq('#content').val())
			}, 500);
			var isSubmitButtonClicked = false;
			jq('#submitButton').bind('click', function() {
				if (isSubmitButtonClicked || !module.exports.checkForm()) {
					return false
				}
				var opt = {
					success: function(re) {
						var status = parseInt(re.errCode);
						if (status == 0) {
							clearInterval(timer);
							
							localStorage.removeItem(storageKey)
						} else {
							isSubmitButtonClicked = false
						}
					},
					error: function(re) {
						isSubmitButtonClicked = false
					}
				};
				isSubmitButtonClicked = true;
				jq.DIC.ajaxForm('newthread', opt, true);
				return false
			});
			jq('.cancelBtn').bind('click', function() {
				if (jq('.photoList .attchImg').length > 0) {
					jq.DIC.dialog({
						content: '是否放弃当前内容?',
						okValue: '确定',
						cancelValue: '取消',
						isMask: true,
						ok: function() {
							history.go(-1)
						}
					})
				} else {
					history.go(-1)
				}
			});
			jq('#content').on('focus', function() {
				jq('.bNav').hide()
			}).on('blur', function() {
				jq('.bNav').show()
			});
			module.exports.initUpload();
			module.exports.initModal();
		
			jq(".expreSelect").on("click", function() {
				if (jq.os.ios) {
					emotion.show()
				} else {
					if (jq('.expreList').css('display') != 'none') {
						emotion.hide()
					} else {
						emotion.show()
					}
				}
			});
			jq(".photoSelect").on("click", emotion.hide);
			jq(".tagBox a").on("click", function() {
				jq(".tagBox").find('a').attr('class', '');
				var labelId = jq(this).attr('labelId');
				if (jq('input[name="fId"]').val() != labelId) {
					jq(this).attr('class', 'on');
					jq('input[name="fId"]').val(labelId)
				} else {
					jq('input[name="fId"]').val(0)
				}
			});
			var selTagId = jq.DIC.getQuery('filterType');
			if (selTagId) {
				var tagArr = jq('.tagBox').find('a');
				jq.each(tagArr, function(key, value) {
					jq(value).removeClass('on');
					if (jq(value).attr('labelid') == selTagId) {
						jq(value).addClass('on');
						jq('input[name="fId"]').val(selTagId)
					}
				})
			}
			
			
		},
		initModal: function() {
			jq('#submitButton').bind('touchstart', function() {
				jq(this).addClass('sendOn')
			}).bind('touchend', function() {
				jq(this).removeClass('sendOn')
			});
			jq('#cBtn').bind('touchstart', function() {
				jq(this).addClass('cancelOn')
			}).bind('touchend', function() {
				jq(this).removeClass('cancelOn')
			})
		},
		checkForm: function() {
			jq.each(module.exports.uploadInfo, function(i, n) {
				if (n && !n.isDone) {
					jq.DIC.dialog({
						content: '图片上传中，请等待',
						autoClose: true
					});
					return false
				}
			});
			var content = jq('#content').val();
			var contentLen = jq.DIC.mb_strlen(jq.DIC.trim(content));
			if (contentLen < 15) {
				jq.DIC.dialog({
					content: '内容过短',
					autoClose: true
				});
				return false
			}
			return true
		},
		checkPicSize: function(file) {
			if (file.size > 10000000) {
				return false
			}
			return true
		},
		checkPicType: function(file) {
			return true
		}
	};
	//module = module.exports
	module.exports.init()
