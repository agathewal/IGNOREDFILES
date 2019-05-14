var syntax_sql = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-sql/syntax-sql.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.sql = [
           "Wiky.rules.pre",
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class=\"syntax_sql\">");}}, // style for syntax
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='sqlstr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'/g, tmplt:function($0,$1){return Wiky.store("<span class=\"sqlstr\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/--(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"sqlcmt\">--"+$1+"</span>\xB6");}}, // single line comment
          { rex:/\/\*(.*?)\*\//g, tmplt:function($0,$1){return Wiky.store("<span class\=\"sqlcmt\">\/*"+$1+"*\/</span>");}}, // multi-line comment
          { rex:/\b(ADD|ALL|ALTER|ANALYZE|AND|AS|ASC|ASENSITIVE|BEFORE|BETWEEN|BIGINT|BINARY|BLOB|BOTH|BY|CALL|CASCADE|CASE|CHANGE|CHAR|CHARACTER|CHECK|COLLATE|COLUMN|CONDITION|CONNECTION|CONSTRAINT|CONTINUE|CONVERT|CREATE|CROSS|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|DATABASE|DATABASES|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DEC|DECIMAL|DECLARE|DEFAULT|DELAYED|DELETE|DESC|DESCRIBE|DETERMINISTIC|DISTINCT|DISTINCTROW|DIV|DOUBLE|DROP|DUAL|EACH|ELSE|ELSEIF|ENCLOSED|ESCAPED|EXISTS|EXIT|EXPLAIN|FALSE|FETCH|FLOAT|FOR|FORCE|FOREIGN|FROM|FULLTEXT|GOTO|GRANT|GROUP|HAVING|HIGH_PRIORITY|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|IF|IGNORE|IN|INDEX|INFILE|INNER|INOUT|INSENSITIVE|INSERT|INT|INTEGER|INTERVAL|INTO|IS|ITERATE|JOIN|KEY|KEYS|KILL|LEADING|LEAVE|LEFT|LIKE|LIMIT|LINES|LOAD|LOCALTIME|LOCALTIMESTAMP|LOCK|LONG|LONGBLOB|LONGTEXT|LOOP|LOW_PRIORITY|MATCH|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MIDDLEINT|MINUTE_MICROSECOND|MINUTE_SECOND|MOD|MODIFIES|NATURAL|NOT|NO_WRITE_TO_BINLOG|NULL|NUMERIC|ON|OPTIMIZE|OPTION|OPTIONALLY|OR|ORDER|OUT|OUTER|OUTFILE|PRECISION|PRIMARY|PROCEDURE|PURGE|READ|READS|REAL|REFERENCES|REGEXP|RENAME|REPEAT|REPLACE|REQUIRE|RESTRICT|RETURN|REVOKE|RIGHT|RLIKE|SCHEMA|SCHEMAS|SECOND_MICROSECOND|SELECT|SENSITIVE|SEPARATOR|SET|SHOW|SMALLINT|SONAME|SPATIAL|SPECIFIC|SQL|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_BIG_RESULT|SQL_CALC_FOUND_ROWS|SQL_SMALL_RESULT|SSL|STARTING|STRAIGHT_JOIN|TABLE|TERMINATED|THEN|TINYBLOB|TINYINT|TINYTEXT|TO|TRAILING|TRIGGER|TRUE|UNDO|UNION|UNIQUE|UNLOCK|UNSIGNED|UPDATE|USAGE|USE|USING|UTC_DATE|UTC_TIME|UTC_TIMESTAMP|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|WHEN|WHERE|WHILE|WITH|WRITE|XOR|YEAR_MONTH|ZEROFILL)\b/gi, tmplt:function($0,$1){return Wiky.store("<span class\=\"sqlkwd\">"+$1+"</span>");}}, // keywords
          { rex:/([^@])\b([0-9]+)\b([^@])/g, tmplt:function($0,$1,$2,$3){return Wiky.store($1+"<span class\=\"sqlnum\">"+$2+"</span>"+$3);}}, // numbers @ needed for Wiky.store
          "Wiky.rules.post",
    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.sql);        
    },
    
    addstyle:function(s){
        if (ArrayContains(css_urls,s)) return;
        css_urls.push(s);
        var css=document.createElement("link")
        css.setAttribute("rel", "stylesheet")
        css.setAttribute("type", "text/css")
        css.setAttribute("href", s);
        document.getElementsByTagName("head")[0].appendChild(css);
    }
}
