import tkinter as tk
from tkinter import messagebox
import math

# --- Fenêtre principale ---
root =tk.Tk()
root.title("Calculatrice")
root.geometry("400x500")
root.resizable(False,False)

# --- Écran d'affichage ---
entry = tk.Entry(root,font=('Arial',24),borderwidth=2,relief='ridge',justify='center')
entry.grid(row=0, column=0, columnspan=4, padx=10, pady=20)

# --- Fonctions ---
def click_button(valeur):
    current =entry.get()
    entry.delete(0,tk.END)
    entry.insert(0,current+str(valeur))

def clear():
    entry.delete(0,tk.END)

def calculate():
    try:
        expression =entry.get()
        if '√' in expression:
            expression =expression.replace('√','math.sqrt')
        result = eval(expression)
        entry.delete(0,tk.END)
        entry.insert(0,str(result))
    except Exception:
        messagebox.showerror("Error","Entre Invalide")
        entry.delete(0,tk.END)
        
def change_sign():
    current =entry.get()
    if current:
        if current.startswith('-'):
            entry.delete(0)
            entry.insert(0,current[1:])
        else:
            entry.delete(0)
            entry.insert(0,'-'+current)

def precent():
    try:
        value =float(entry.get())
        entry.delete(0,tk.END)
        entry.insert(0,str(value/100))
    except Exception:
        messagebox.showerror("Error","Entre Invalide")
        entry.delete(0,tk.END)
        
buttons  =[
    'C', '±', '%', '√',
    '7', '8', '9', '/',
    '4', '5', '6', '*',
    '1', '2', '3', '-',
    '0', '.', '=', '+'
]
row =1
col =0

for button in buttons:
    if button == '=':
        b = tk.Button(root,text=button,height=3,width=10,command=calculate)
    elif button == 'C':
        b = tk.Button(root,text=button,height=3,width=10,command=clear)
    elif button == '±':
        b = tk.Button(root,text=button,height=3,width=10,command=change_sign)
    elif button == '%':
        b = tk.Button(root,text=button,height=3,width=10,command=precent)
    else:
        b = tk.Button(root, text=button, width=10, height=3, command=lambda x=button: click_button(x))

    b.grid(row=row, column=col, padx=5, pady=5)

    col += 1
    if col > 3:
        col = 0
        row += 1

root.mainloop()
