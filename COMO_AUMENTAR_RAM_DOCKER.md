# 📖 CÓMO AUMENTAR LA RAM DE DOCKER (Fácil)

## Método 1: Desde Docker Desktop (Más Fácil) ⭐

### Paso 1: Abre Docker Desktop
- Busca "Docker Desktop" en tu menú de inicio de Windows
- Ábrelo (debería estar ejecutándose en la barra de tareas)

### Paso 2: Ve a Configuración
1. Haz clic en el **ícono de engranaje** ⚙️ en la esquina superior derecha
   - O haz clic derecho en el icono de Docker en la barra de tareas
   - Selecciona "Settings"

### Paso 3: Ve a Recursos Avanzados
1. En el menú lateral izquierdo, haz clic en **"Resources"**
2. Luego haz clic en **"Advanced"** (Avanzado)

### Paso 4: Aumenta la Memoria
1. Busca el slider que dice **"Memory"** (Memoria)
2. Verás que actualmente está en **3.77 GB** (o similar)
3. **Mueve el slider hacia la derecha** hasta llegar a **6 GB** u **8 GB**
   - Recomendación: **8 GB** para mejor rendimiento
   - Mínimo: **6 GB** para que funcione

### Paso 5: Aplica los Cambios
1. Haz clic en el botón **"Apply & Restart"** (Aplicar y Reiniciar)
2. Docker se cerrará y se reiniciará automáticamente
3. **Espera 2-3 minutos** hasta que Docker esté listo de nuevo

### Paso 6: Verifica que Funcione
1. Abre una terminal en tu proyecto
2. Ejecuta:
   ```bash
   docker-compose up -d
   ```
3. Abre tu navegador en: http://localhost:8080
4. ¡Debería funcionar! ✅

---

## Método 2: Crear Archivo .wslconfig (Alternativa)

Si el método 1 no funciona, puedes crear este archivo:

### Paso 1: Abre PowerShell como Administrador
1. Presiona `Windows + X`
2. Selecciona **"Windows PowerShell (Admin)"** o **"Terminal (Admin)"**

### Paso 2: Navega a tu carpeta de usuario
```powershell
cd $env:USERPROFILE
```

### Paso 3: Crea el archivo .wslconfig
```powershell
notepad .wslconfig
```

### Paso 4: Pega este contenido
```ini
[wsl2]
memory=8GB
swap=2GB
processors=4
```

### Paso 5: Guarda y Cierra
1. Presiona `Ctrl + S` para guardar
2. Cierra el Bloc de notas

### Paso 6: Reinicia WSL
```powershell
wsl --shutdown
```

### Paso 7: Reinicia Docker Desktop
1. Cierra Docker Desktop completamente
2. Ábrelo de nuevo
3. Espera a que se inicie

---

## ¿Cómo saber si funcionó?

### Método 1: Verificar desde Docker
1. Abre Docker Desktop
2. Ve a **Settings** → **Resources** → **Advanced**
3. Debería mostrar **"Memory: 8 GB"** (o lo que configuraste)

### Método 2: Verificar desde Terminal
```bash
docker stats --no-stream
```
Deberías ver algo como:
```
NAME                 MEM USAGE / LIMIT
ecommerce_app        150MiB / 8GiB    ← Ahora dice 8GB
```

---

## 🔧 Si sigues teniendo problemas

### Opción A: Usar Sail con menos recursos
Modifica temporalmente `docker-compose.yml`:

```yaml
services:
  app:
    environment:
      - OPCACHE_ENABLE=0  # Deshabilitar cache de PHP
```

### Opción B: Usar servidor local (sin Docker)
1. Instala XAMPP (incluye PHP + MySQL)
2. Descarga: https://www.apachefriends.org/
3. Instálalo con las opciones por defecto
4. En tu proyecto:
   ```bash
   # Actualizar .env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecommerce

   # Instalar dependencias sin Docker
   composer install
   npm install

   # Ejecutar servidor local
   php artisan serve
   ```
5. Abre: http://localhost:8000

---

## 📞 Resumen Rápido

**El método más fácil es el Método 1:**

1. Abre Docker Desktop
2. Settings (engranaje ⚙️)
3. Resources → Advanced
4. Mueve el slider de Memory a 8 GB
5. "Apply & Restart"
6. Espera 2-3 minutos
7. ¡Listo!

Esto solucionará el error 502 Bad Gateway inmediatamente.
