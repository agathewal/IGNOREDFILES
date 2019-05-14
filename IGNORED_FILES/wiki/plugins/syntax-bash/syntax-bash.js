var syntax_bash = {
    id:null,
    parms:null,
    element:null,
    html:null,
    
    init:function(id,parms){
        this.id=id;
        this.parms=parms;
        this.element = document.getElementById(this.id);
        this.addstyle("plugins/syntax-bash/syntax-bash.css");
        this.html = this.element.innerHTML;
        Wiky.rules.lang.bash = [
           "Wiky.rules.pre",
          { rex:/#(.*?)(?:\xB6|$)/g, tmplt:function($0,$1){return Wiky.store("<span class\='bashcmt'>#"+$1+"</span>\xB6");}}, // single line comment
          { rex:/<pre>/g, tmplt:function($0,$1){return Wiky.store("<pre class='syntax_bash'>");}}, // pre
          { rex:/((?:[A-Za-z]|_)[A-Za-z0-9_]*(?==)|\$\{(?:[^ \t]+)\}|\$\((?:[^ \t]+)\)|\$(?:[A-Za-z]|_)[A-Za-z0-9_]*|\$(?:[^ \t]{1}))(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class=\"bashvar\">"+$1+"</span>");}}, // variable
          { rex:/((?:[A-Za-z]|_)[A-Za-z0-9_]*)(?==)(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='bashvar'>"+$1+"</span>");}}, // variable
          { rex:/'([^'\\\xB6]*(\\.[^'\\\xB6]*)*)'(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class=\"bashstr\">\'"+$1+"\'</span>");}}, // string delimited by "'" with "\'" allowed ..
          { rex:/"([^"\\\xB6]*(\\.[^"\\\xB6]*)*)"(?=[^>]*<)/g, tmplt:function($0,$1){return Wiky.store("<span class='bashstr'>\""+$1+"\"</span>");}}, // string delimited by '"' with '\"' allowed ..
          { rex:/((?:[A-Za-z]|_)[A-Za-z0-9_]*)(?= in )/g, tmplt:function($0,$1){return Wiky.store("<span class='bashvar'>"+$1+"</span>");}}, // variable
          { rex:/\b(adduser|addgroup|alias|apropos|apt-get|aspell|awk|basename|bash|bc|bg|bind|break|builtin|bzip2|cal|caller|case|cat|cd|cfdisk|chgrp|chmod|chown|chroot|chkconfig|cksum|clear|cmp|comm|command|compgen|complete|continue|cp|cron|crontab|csplit|cut|date|dc|dd|ddrescue|declare|df|diff|diff3|dig|dir|dircolors|dirname|dirs|disown|dmesg|do|done|du|echo|egrep|eject|elif|else|enable|esac|env|ethtool|eval|exec|exit|expect|expand|export|expr|false|fdformat|fc|fdisk|fg|fgrep|file|fi|find|fmt|fold|for|format|free|fsck|ftp|function|fuser|gawk|getopts|grep|groups|gzip|hash|head|help|history|hostname|id|if|ifconfig|ifdown|ifup|import|in|install|jobs|join|kill|killall|less|let|ln|local|locate|logname|logout|look|lpc|lpr|lprint|lprintd|lprintq|lprm|ls|lsof|make|man|mkdir|mkfifo|mkisofs|mknod|more|mount|mtools|mv|mmv|netstat|nice|nl|nohup|nslookup|open|op|passwd|paste|pathchk|ping|pkill|popd|pr|printcap|printenv|printf|ps|pushd|pwd|quota|quotachec|quotactl|ram|rcp|read|readarray|readonly|reboot|rename|renice|remsync|return|rev|rm|rmdir|rsync|screen|scp|sdiff|sed|select|seq|set|sftp|shift|shopt|shutdown|sleep|slocate|sort|source|split|ssh|strace|su|sudo|sum|suspend|symlink|sync|tail|tar|tee|test|then|time|times|touch|top|tracerout|trap|tr|true|tsort|tty|type|typeset|ulimit|umask|umount|unalias|uname|unexpand|uniq|units|unset|unshar|until|useradd|usermod|users|uuencode|uudecode|vdir|vi|vmstat|wait|watch|wc|whereis|which|while|who|whoami|Wget|write|xargs)\b/g, tmplt:function($0,$1){return Wiky.store("<span class\=\"bashkwd\">"+$1+"</span>");}}, // keywords
          "Wiky.rules.post",    
        ];
    },
    
    render:function(){
        Wiky.blocks = [];
        this.element.innerHTML = Wiky.apply(this.html, Wiky.rules.lang.bash);        
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
