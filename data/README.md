该目录用于存放数据文件，分析前的文件结构为：

	
	|-xxxx(年)xx(月)xx(日)
		|----xxx(流程名).data
		|----xxxx(流程名).data
		.
		.
		.
		\---xxxx(流程名).data
	|-xxxx(年)xx(月)xx(日)
		...
	
经过解析后的数据将会自动删除`.data`结尾的文件，追加到`.prease`后缀的同名文件中;

	
	|-xxxx(年)xx(月)xx(日)
		|----xxx(流程名).data
		|----xxxx(流程名).data
		.
		.
		.
		\---xxxx(流程名).data
	|-xxxx(年)xx(月)xx(日)
		...
