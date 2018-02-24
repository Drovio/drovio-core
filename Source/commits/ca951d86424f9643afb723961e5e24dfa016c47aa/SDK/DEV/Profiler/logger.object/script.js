// Create Logger Object
logger = {
	status : function() {
		return (cookies.get("lggr") == "");
	},
	log : function(content) {
		if (this.status() || debuggr.status())
			console.log(content);
	},
	dir : function(content) {
		if (this.status() || debuggr.status())
			console.dir(content);
	},
	dirxml : function(content) {
		if (this.status() || debuggr.status())
			console.dirxml(content);
	}
};