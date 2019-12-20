[[ -s ~/.bashrc ]] && source ~/.bashrc

#alfred; command:ll; parameters: none; description: list folder
alias ll='ls -alh'
alias less='less -FSRXc'                    # Preferred 'less' implementation

alias mkdir='mkdir -pv'  # Preferred 'mkdir' implementation

alias alfred_help='echo "This is a alfred help message"'  #alias comment
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

#alfred; command:vg; parameters:up, destroy, halt, ssh; description: vagrant command on homestead
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
