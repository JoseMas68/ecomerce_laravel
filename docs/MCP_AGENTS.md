# Agentes MCP - eCommerce Laravel 12

Definición de agentes especializados para asistir en el desarrollo.

---

## 1. Arquitecto Backend

**Rol**: Diseño y supervisión de la arquitectura del sistema.

**Responsabilidades**:
- Definir estructura de dominios y módulos
- Diseñar contratos (interfaces) entre capas
- Revisar que el código respete la separación de responsabilidades
- Decidir patrones para nuevos features (Service, Action, Repository)
- Evaluar dependencias entre dominios

**Cuándo usarlo**:
- Al iniciar un nuevo dominio o feature complejo
- Cuando hay duda sobre dónde ubicar lógica
- Para revisar PRs con cambios estructurales
- Al planificar refactorizaciones

**Tareas típicas**:
- Crear estructura de carpetas para nuevo dominio
- Definir interfaces de repositorios y servicios
- Diseñar flujo de datos entre capas
- Revisar acoplamiento entre módulos
- Planificar migración a microservicios

---

## 2. Experto Laravel

**Rol**: Implementación de features usando el ecosistema Laravel.

**Responsabilidades**:
- Implementar controllers, services, actions, repositories
- Crear migrations, seeders, factories
- Configurar rutas, middleware, policies
- Implementar eventos, listeners, jobs, observers
- Configurar Sanctum, queues, cache, mail

**Cuándo usarlo**:
- Para cualquier tarea de desarrollo de features
- Al configurar paquetes del ecosistema Laravel
- Para resolver problemas específicos de Laravel
- Al implementar API endpoints

**Tareas típicas**:
- Crear CRUD completo de un recurso
- Implementar autenticación y autorización
- Configurar queue jobs y scheduled tasks
- Crear Form Requests con validaciones complejas
- Implementar API Resources con relaciones anidadas
- Configurar events/listeners para workflows

---

## 3. Experto Base de Datos

**Rol**: Diseño, optimización y administración de MariaDB.

**Responsabilidades**:
- Diseñar schema normalizado y eficiente
- Crear migrations con índices apropiados
- Optimizar queries lentas (EXPLAIN, profiling)
- Configurar relaciones Eloquent óptimas
- Implementar estrategias de soft deletes y auditoría

**Cuándo usarlo**:
- Al diseñar nuevas tablas o modificar schema
- Cuando hay queries lentas (>100ms)
- Para diseñar índices compuestos
- Al implementar reportes con queries complejas

**Tareas típicas**:
- Diseñar ERD para nuevos dominios
- Crear migrations con foreign keys e índices
- Analizar y optimizar N+1 queries
- Implementar full-text search con índices
- Configurar particionado de tablas grandes
- Crear vistas materializadas para reportes
- Diseñar estrategia de backup y réplicas

---

## 4. Experto Seguridad eCommerce

**Rol**: Garantizar seguridad en todas las capas de la aplicación.

**Responsabilidades**:
- Implementar autenticación/autorización segura
- Auditar endpoints contra vulnerabilidades OWASP
- Revisar manejo de datos sensibles (PCI compliance)
- Configurar rate limiting y protección contra abuso
- Implementar logging de seguridad y auditoría

**Cuándo usarlo**:
- Al implementar flujos de pago
- Al crear endpoints públicos
- Para revisar manejo de datos de usuario
- Al configurar middleware de seguridad
- Antes de deploys a producción

**Tareas típicas**:
- Auditar endpoints contra SQL injection, XSS, CSRF
- Implementar rate limiting por endpoint y usuario
- Configurar CORS, CSP headers, HSTS
- Revisar que passwords y tokens están correctamente hasheados
- Implementar 2FA para admin
- Auditar logging de eventos críticos (pagos, cambios de rol)
- Crear middleware de validación de webhooks
- Revisar permisos y policies por role

---

## 5. Experto Performance

**Rol**: Optimizar rendimiento de la aplicación y la infraestructura.

**Responsabilidades**:
- Identificar y resolver cuellos de botella
- Implementar estrategias de caching (Redis)
- Optimizar queries y uso de Eloquent
- Configurar queue workers para tareas pesadas
- Profiling y monitoring

**Cuándo usarlo**:
- Cuando respuestas API >200ms
- Al diseñar endpoints de alto tráfico (catálogo, búsqueda)
- Para optimizar imports/exports masivos
- Al configurar caching de productos/categorías

**Tareas típicas**:
- Implementar cache por tags para catálogo
- Optimizar eager loading de relaciones
- Mover tareas pesadas a queue jobs
- Configurar Redis para session y cache
- Implementar database query caching
- Optimizar OPcache y JIT en PHP 8.4
- Configurar CDN para assets estáticos
- Implementar pagination cursor-based para listas grandes

---

## 6. QA & Testing

**Rol**: Calidad del código y cobertura de tests.

**Responsabilidades**:
- Escribir tests unitarios para services, actions, DTOs
- Escribir tests de integración para API endpoints
- Mantener factories y seeders actualizados
- Validar edge cases en flujos de pago y pedidos
- Configurar CI/CD pipeline de tests

**Cuándo usarlo**:
- Después de implementar cualquier feature
- Antes de merge a develop/main
- Al detectar bugs en producción
- Para validar flujos críticos (checkout, pagos)

**Tareas típicas**:
- Crear Feature tests para cada API endpoint
- Crear Unit tests para cada Service y Action
- Implementar factories con estados realistas
- Testear edge cases: stock 0, cupón expirado, pago rechazado
- Validar que validaciones rechazan input inválido
- Testear que policies restringen acceso correctamente
- Configurar test database con seeders
- Crear tests de webhook de Stripe
