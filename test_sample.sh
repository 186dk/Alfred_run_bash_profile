[[ -s ~/.bashrc ]] && source ~/.bashrc

#alfred; command:ll; parameters: none; description: list folder
alias ll='ls -alh'

# Preferred 'less' implementation
# Implement with FSRXc
alias less='less -FSRXc'
alias mkdir='mkdir -pv'  # Preferred 'mkdir' implementation
alias alfred_help='echo "This is a alfred help message"'  #alias comment

trash () { command mv "$@" ~/.Trash ; }     # trash:        Moves a file to the MacOS trash


#   -----------------------------
#   NOT Function comment
#   -----------------------------

# Count of non-hidden files in current dir
# parameters: none | path
# $1 is the directory to count
numFiles(){
  cd "$1" || return
  echo $(ls -1 | wc -l)
}

# Opens any file in MacOS Quicklook Preview
# parameters: file
ql () { qlmanage -p "$*" >& /dev/null; }

## alfred reserve keywords ##
#alfred; command:killp; parameters:port number; description: kill process with given port number
killp(){
    pid=$(lsof -i:"$1" | grep LISTEN | awk '{print $2}')
    if [[ -z "$pid" ]]
    then
      echo "No found any process with port $1"
    else
      kill -9 "$pid"
      echo "Killed process $pid"
    fi
}

#alfred; command:vg; parameters:up| destroy| halt| ssh; description: vagrant command on homestead
function vg() {
    cd ~/Homestead || exit
    vagrant "$*"
}

#alfred; command:cl; parameters:path; description: cd to path and ls
function cl() {
    DIR="$*";
        # if no DIR given, go home
        if [[ $# -lt 1 ]]; then
                DIR=$HOME;
    fi;
    builtin cd "${DIR}" && \
    # use your preferred ls command
        ls -alh
}
