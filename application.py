#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Entry point para executar app.py no Superdomínios.
Compatível com wsgi/passenger e servidor HTTP embutido.
"""

import sys
import os

# Garante que o caminho correto está no sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app import main

if __name__ == "__main__":
    main()
