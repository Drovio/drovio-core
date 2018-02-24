debuggr = {
	status : function() {
		var debuggerStatus = cookies.get("dbgr");
		return (debuggerStatus != null);
	}
}