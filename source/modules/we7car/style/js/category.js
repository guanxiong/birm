var CFD = "不限";
var CSD = "不限";
var ShowT = 0;


var CFData = [];
var CSData = [];
var CAData ='';

function CS() {
    var CLIST = arguments[4];
    if (ShowT)
    CLIST = CFD + "$" + CSD + "#" + CLIST;
    CAData = CLIST.split("#");
    for (var i = 0; i < CAData.length; i++) {
        parts = CAData[i].split("@");
        CFData[i] = parts[0];
		if (typeof(parts[1]) != "undefined") CSData[i] = parts[1].split("$");
    }
     
    var self = this;
    this.SelF = document.getElementById(arguments[0]);
    this.SelS = document.getElementById(arguments[1]);
    this.DefF = arguments[2]; 
    this.DefS = arguments[3];
    this.SelF.CS = this;
    this.SelS.CS = this;
    this.SelF.onchange = function () {
        CS.SetS(self)
    };
     CS.SetF(this)
};
CS.SetF = function (self) {
    for (var i = 0; i < CFData.length; i++) {
        var title, value;
        title = CFData[i].split("-")[0];
        value = CFData[i].split("-")[1];
        if (title == CFD) { value = "" }
		if (value.indexOf('$') != '-1') { value = "0" }
        self.SelF.options.add(new Option(title, value));
        if (self.DefF == value) { self.SelF[i].selected = true }
    }
    CS.SetS(self)
};
CS.SetS = function (self) {
    var fi = self.SelF.selectedIndex;
    var slist = CSData[fi];
    self.SelS.length = 0;
    if (self.SelF.value != "" && ShowT) {
        self.SelS.options.add(new Option(CSD, ""))
    }
	if (typeof(slist) != "undefined") {
		for (var i = 1; i < slist.length; i++) {
			var title, value;
			title = slist[i].split("-")[0];
			value = slist[i].split("-")[1];
			if (title == CSD) { value = "" }
			self.SelS.options.add(new Option(title, value));
			if (self.DefS == value) {
				self.SelS[i-1].selected = true;
			}
		}
	}
}