# Alfred bash profile workflow


The workflow enable Alfred run alias or functions in ~/.bash_profile.

## Install

0. Downlaod and install the workflow.
0. Add comment tags for your alias or functions in .bash_profile with following format:

```bash
#alfred; command: XXX ; parameters: XXX or 'none', ('none' means no parameter); description: XXX
```
Where keyword

'command' is alias or function name.  
'parameters' is alias or function parameters. If it doesn't have parameters, you need to give 'none'.   
'description' is alias or function description.


## Run

Run alfred and type ! 