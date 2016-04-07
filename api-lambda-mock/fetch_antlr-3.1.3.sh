wget http://www.antlr3.org/download/antlr-3.1.3.tar.gz
tar xfz antlr-3.1.3.tar.gz
(
    cd antlr-3.1.3/runtime/Python/
    python setup.py install
)
pip install java2python

