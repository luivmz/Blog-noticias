#!/usr/bin/env python3
"""Medición reproducible de pares usados por el CSS; no es una auditoría WCAG."""
def luminance(hex_color):
    channels = [int(hex_color[i:i+2],16)/255 for i in (1,3,5)]
    channels = [c/12.92 if c<=0.04045 else ((c+0.055)/1.055)**2.4 for c in channels]
    return sum(c*w for c,w in zip(channels,[0.2126,0.7152,0.0722]))
pairs=[('Texto / fondo','#232a27','#f8f6f0',4.5),('Texto secundario / fondo','#535b56','#f8f6f0',4.5),('Acento / fondo','#245643','#f8f6f0',4.5),('Botón normal','#ffffff','#245643',4.5),('Botón hover','#ffffff','#193d30',4.5),('Error / blanco','#9d2824','#ffffff',4.5),('Correcto / blanco','#1e553b','#ffffff',4.5),('Texto / búsqueda','#232a27','#eeeee5',4.5),('Borde / fondo','#858b81','#f8f6f0',3),('Foco / fondo','#174fbb','#f8f6f0',3),('Foco / blanco','#174fbb','#ffffff',3),('Borde input / blanco','#535b56','#ffffff',3)]
for name,fg,bg,minimum in pairs:
    a,b=sorted([luminance(fg),luminance(bg)])
    ratio=(b+.05)/(a+.05)
    print(f'{name}: {fg} sobre {bg} = {ratio:.2f}:1; referencia {minimum}:1; {"PASS" if ratio>=minimum else "FAIL"}')
