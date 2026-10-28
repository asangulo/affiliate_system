<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeId = ref('roles');

const setActive = (id) => { activeId.value = id; };

const roles = [
    { badge: 'super_admin', color: 'navy',  titulo: 'Super Admin', texto: 'Acceso total al sistema: afiliados, cartera, pagos, sedes, historial, reportes, usuarios y roles.' },
    { badge: 'empleado',    color: 'blue',  titulo: 'Empleado', texto: 'Gestión diaria: afiliados, beneficiarios, importación desde Excel, cartera, pagos y reportes. No administra usuarios ni sedes.' },
    { badge: 'cliente',     color: 'green', titulo: 'Cliente', texto: 'Es el titular del contrato. Su cuenta se crea sola: entra con su usuario (nombre.apellido), consulta su plan ("Mi Plan"), su grupo familiar y sus propios pagos ("Mis Pagos"). No ve información de otros afiliados.' },
];

const secciones = [
    {
        id: 'acceso',
        titulo: 'Ingresar y contraseña',
        badge: 'Todos los roles',
        ruta: 'profile.edit',
        rutaLabel: 'Ir a Mi Perfil',
        intro: 'Cómo entrar al sistema y cómo cambiar tu contraseña.',
        flujos: [
            {
                titulo: 'Iniciar sesión',
                pasos: [
                    'En la pantalla de ingreso escribe tu correo o tu usuario en el primer campo, y tu contraseña en el segundo.',
                    'Los titulares (clientes) entran con su usuario: primer nombre, un punto y primer apellido. Ejemplo: alex.angulo (sin tildes y en minúsculas).',
                    'La contraseña inicial de los titulares es la que les entrega Horizonte S.A.S. Si dos titulares tienen el mismo nombre y apellido, al segundo se le agrega un número (alex.angulo2).',
                ],
            },
            {
                titulo: 'Cambiar la contraseña',
                pasos: [
                    'En tu primer ingreso el sistema te pedirá cambiar la contraseña inicial: es obligatorio y no podrás ver nada más hasta hacerlo.',
                    'Para cambiarla después, entra a "Mi Perfil" y usa el formulario "Actualizar contraseña". Debes escribir la contraseña actual y una nueva diferente.',
                    'Si olvidaste tu contraseña, comunícate con Horizonte S.A.S: un Super Admin puede asignarte una nueva desde Usuarios. La opción de recuperarla por correo solo sirve si tu cuenta tiene un correo real.',
                ],
            },
        ],
    },
    {
        id: 'afiliados',
        titulo: 'Afiliados',
        badge: 'Super Admin y Empleado',
        ruta: 'affiliates.index',
        rutaLabel: 'Ir a Afiliados',
        intro: 'Aquí se registran y administran las personas afiliadas y sus beneficiarios.',
        flujos: [
            {
                titulo: 'Registrar un nuevo afiliado',
                pasos: [
                    'Entra a Afiliados y haz clic en "Nuevo Afiliado".',
                    'Completa cédula, nombre, sede, plan, valor de la cuota y fecha de afiliación.',
                    'Si tiene beneficiarios, agrégalos en la misma pantalla (nombre, parentesco, edad, auxilio).',
                    'Guarda — quedará activo y visible de inmediato en el listado.',
                    'El sistema crea automáticamente el usuario del titular para el portal y te muestra su nombre de usuario en el mensaje de confirmación.',
                ],
            },
            {
                titulo: 'Editar un afiliado o su grupo familiar',
                pasos: [
                    'Busca al afiliado por nombre, cédula o contrato.',
                    'Entra a "Editar" y actualiza los datos que cambiaron.',
                    'Los beneficiarios que quites de la lista no se borran: quedan marcados como inactivos para no perder el historial.',
                ],
            },
            {
                titulo: 'Importar varios afiliados desde Excel',
                pasos: [
                    'Haz clic en "Importar Excel" en la parte superior del listado.',
                    'Selecciona el archivo .xlsx con las columnas esperadas por la plantilla.',
                    'Confirma — si alguna fila falla, el resto se importa igual y se avisa cuál tuvo error.',
                    'Cada titular nuevo del archivo recibe su usuario del portal de forma automática. Los que ya tenían usuario no se tocan. Para ver el usuario de un titular, abre "Ver" en el listado.',
                ],
            },
            {
                titulo: 'Registrar el fallecimiento del titular',
                pasos: [
                    'En el listado, busca al titular y haz clic en "Fallecido" (solo aparece en afiliados activos).',
                    'Indica la fecha de fallecimiento y, si quieres, una observación (por ejemplo, el certificado de defunción).',
                    'Elige qué beneficiario pasa a ser el nuevo titular, o "Ninguno (cerrar contrato)" si nadie lo reemplaza.',
                    'Si eliges a un beneficiario, escribe su cédula (obligatoria). Fecha de nacimiento, teléfono y correo son opcionales.',
                    'Al confirmar, el contrato sigue siendo el mismo: se conservan los pagos, el plan y la cuota. El beneficiario elegido deja de figurar como beneficiario y pasa a ser el titular.',
                    'El titular fallecido queda guardado en "Historial de titulares" (al editar el afiliado) con su nombre, cédula y fecha. Su cuenta del portal se desactiva y el nuevo titular recibe la suya, con su propio usuario.',
                    'Si eliges "Ninguno", el afiliado queda inactivo.',
                ],
            },
            {
                titulo: 'Activar o inactivar un afiliado',
                pasos: [
                    'Nunca se elimina un afiliado, se inactiva: así no se pierde el histórico de pagos.',
                    'En el listado, usa el botón "Inactivar" o "Activar" según el caso.',
                    'Un afiliado inactivo no aparece en Cartera ni en los reportes de cobro activo.',
                ],
            },
        ],
    },
    {
        id: 'cartera',
        titulo: 'Cartera',
        badge: 'Super Admin y Empleado',
        ruta: 'payments.portfolio',
        rutaLabel: 'Ir a Cartera',
        intro: 'Muestra quién está al día y quién debe, calculado automáticamente — no hay que llevarlo a mano.',
        flujos: [
            {
                titulo: 'Cómo se calcula la mora',
                pasos: [
                    'Cada pago cubre un mes exacto (un mes = una cuota).',
                    'Cuotas esperadas = meses transcurridos desde la fecha de afiliación hasta hoy.',
                    'Cuotas pagadas = cuántos pagos tiene registrados el afiliado.',
                    'Mora = cuotas esperadas − cuotas pagadas. Saldo = mora × valor de la cuota.',
                ],
            },
            {
                titulo: 'Consultar y filtrar la cartera',
                pasos: [
                    'Busca por nombre, cédula o contrato, o filtra por sede.',
                    'Filtra por "Al día" o "En mora" para priorizar la gestión de cobro.',
                    'Haz clic en un afiliado para ver su detalle completo de pagos.',
                ],
            },
        ],
    },
    {
        id: 'pagos',
        titulo: 'Pagos',
        badge: 'Super Admin y Empleado',
        ruta: 'payments.index',
        rutaLabel: 'Ir a Pagos',
        intro: 'Aquí se registran los pagos que los afiliados hacen manualmente (no hay pasarela de pagos en línea).',
        flujos: [
            {
                titulo: 'Registrar un pago',
                pasos: [
                    'Haz clic en "Registrar pago".',
                    'Selecciona el afiliado, el mes y año que cubre el pago, y el valor recibido.',
                    'Indica la forma de pago (efectivo, transferencia, Nequi, Daviplata, etc.) y guarda.',
                    'El sistema no deja registrar dos veces el mismo mes para el mismo afiliado.',
                ],
            },
            {
                titulo: 'Anular un pago',
                pasos: [
                    'Entra al detalle de pagos del afiliado (desde Cartera o desde Pagos).',
                    'Haz clic en "Anular" junto al pago equivocado.',
                    'Queda registrado en el Historial quién lo anuló y cuándo.',
                ],
            },
        ],
    },
    {
        id: 'mi-plan',
        titulo: 'Mi Plan y Mis Pagos',
        badge: 'Solo Cliente',
        ruta: 'portal.index',
        rutaLabel: 'Ir a Mi Plan',
        intro: 'Es lo que ve un afiliado cuando inicia sesión con su propia cuenta.',
        flujos: [
            {
                titulo: 'Qué puede ver el cliente',
                pasos: [
                    '"Mi Plan": su plan contratado, valor de la cuota, sede, fecha de afiliación, contrato y su grupo familiar.',
                    '"Mis Pagos": cuántas cuotas ha pagado, cuánto debe y la fecha de su próxima cuota.',
                ],
            },
            {
                titulo: 'Cómo obtiene su cuenta un titular',
                pasos: [
                    'La cuenta se crea sola cuando el titular se importa por Excel, se registra a mano o pasa a ser titular por fallecimiento del anterior. Ya está vinculada a su afiliación.',
                    'Su usuario es el primer nombre, un punto y el primer apellido (ej. alex.angulo). Puedes consultarlo en "Ver" dentro de Afiliados, o en el listado de Usuarios.',
                    'Debe cambiar la contraseña inicial en su primer ingreso (ver "Ingresar y contraseña").',
                    'Si un cliente ve "Tu cuenta aún no está vinculada a un plan", un Super Admin puede vincularla desde Usuarios.',
                ],
            },
        ],
    },
    {
        id: 'sedes',
        titulo: 'Sedes',
        badge: 'Solo Super Admin',
        ruta: 'branches.index',
        rutaLabel: 'Ir a Sedes',
        intro: 'Cada afiliado pertenece a una sede (Fusagasugá, Cabrera, Pandi, Granada o Venecia).',
        flujos: [
            {
                titulo: 'Crear o editar una sede',
                pasos: [
                    'Haz clic en "Crear sede" y completa nombre, ciudad, dirección, teléfono y encargado.',
                    'Para editar, entra a la sede desde el listado y actualiza los datos.',
                ],
            },
            {
                titulo: 'Cerrar una sede',
                pasos: [
                    'Si la sede tiene afiliados asociados, no se puede eliminar — solo se puede desactivar ("Cerrar").',
                    'Una sede cerrada deja de aparecer como opción al crear nuevos afiliados.',
                ],
            },
        ],
    },
    {
        id: 'planes',
        titulo: 'Planes',
        badge: 'Solo Super Admin',
        ruta: 'plans.index',
        rutaLabel: 'Ir a Planes',
        intro: 'Aquí se administran los planes que se ofrecen a los afiliados (por ejemplo, Básico o Premium).',
        flujos: [
            {
                titulo: 'Crear o editar un plan',
                pasos: [
                    'Haz clic en "Nuevo plan", escribe el nombre, el valor base de la cuota y, si quieres, una descripción.',
                    'Para cambiar uno existente, usa "Editar" en su fila.',
                    'El valor base se sugiere al registrar un afiliado nuevo con ese plan. Cambiarlo no modifica la cuota de los afiliados que ya existen.',
                    'No pueden existir dos planes con el mismo nombre.',
                ],
            },
            {
                titulo: 'Desactivar o eliminar un plan',
                pasos: [
                    'Un plan inactivo deja de aparecer al registrar afiliados, pero los afiliados que ya lo tienen lo conservan.',
                    'Solo se puede eliminar un plan que no tenga afiliados; si los tiene, desactívalo.',
                    'Al importar un Excel, si el plan no existe todavía, se crea automáticamente y luego puedes ajustar su valor base aquí.',
                ],
            },
        ],
    },
    {
        id: 'historial',
        titulo: 'Historial',
        badge: 'Solo Super Admin',
        ruta: 'activity.index',
        rutaLabel: 'Ir a Historial',
        intro: 'Registra automáticamente cada creación, edición o eliminación en el sistema — no requiere configuración.',
        flujos: [
            {
                titulo: 'Qué queda registrado',
                pasos: [
                    'Cada vez que se crea, edita o elimina un afiliado, beneficiario, pago, sede, plan o usuario.',
                    'Se guarda quién lo hizo, cuándo, y qué campos cambiaron (valor anterior y nuevo).',
                    'Los cambios de titular por fallecimiento aparecen como "Cambio de titular" y se pueden filtrar por ese tipo.',
                ],
            },
            {
                titulo: 'Filtrar y consultar',
                pasos: [
                    'Filtra por tipo de acción, por módulo, por usuario o por rango de fechas.',
                    'Busca por texto para encontrar un movimiento específico.',
                ],
            },
            {
                titulo: 'Purgar registros antiguos',
                pasos: [
                    'La tabla de historial crece con el tiempo; puede limpiarse desde el botón correspondiente indicando cuántos meses conservar.',
                    'Esto es opcional — con la cantidad de afiliados actual no es urgente hacerlo seguido.',
                ],
            },
        ],
    },
    {
        id: 'reportes',
        titulo: 'Reportes',
        badge: 'Super Admin y Empleado',
        ruta: 'reports.index',
        rutaLabel: 'Ir a Reportes',
        intro: 'Cifras generales del negocio, filtrables por fecha y por sede.',
        flujos: [
            {
                titulo: 'Qué muestra cada indicador',
                pasos: [
                    '"Personas cubiertas": afiliados activos más sus beneficiarios activos.',
                    '"Ingreso mensual esperado" vs. "Recaudado": compara lo que debería entrar contra lo que realmente se ha cobrado en el mes.',
                    '"Estado de cartera": cuántos afiliados están al día, cuántos en mora, y la deuda acumulada total.',
                    '"Afiliados por plan" y "Mayores saldos pendientes": para priorizar gestión comercial y de cobro.',
                ],
            },
        ],
    },
    {
        id: 'usuarios',
        titulo: 'Usuarios y Roles',
        badge: 'Solo Super Admin',
        ruta: 'admin.users.index',
        rutaLabel: 'Ir a Usuarios',
        intro: 'Aquí se crean las cuentas de acceso al sistema (personal interno y clientes) y se les asigna un rol.',
        flujos: [
            {
                titulo: 'Crear un usuario interno (Super Admin o Empleado)',
                pasos: [
                    'Haz clic en "Nuevo Usuario", completa nombre, correo y contraseña.',
                    'Asígnale el rol "Super Admin" o "Empleado" según lo que deba poder hacer.',
                ],
            },
            {
                titulo: 'Cuentas de clientes (titulares)',
                pasos: [
                    'Normalmente no tienes que crearlas: se generan solas al importar, registrar un afiliado o cambiar de titular.',
                    'Solo si hace falta una excepción, crea el usuario con el rol "Cliente" y selecciona el afiliado al que corresponde en el mismo formulario.',
                    'Desde ese momento, ese cliente podrá ver "Mi Plan" y "Mis Pagos" con su información real.',
                ],
            },
            {
                titulo: 'Ayudar a un cliente que olvidó su contraseña',
                pasos: [
                    'Busca al usuario por nombre, correo o nombre de usuario (ej. alex.angulo) y entra a "Editar".',
                    'Escribe una contraseña nueva y guarda. Entrégasela al cliente por un medio seguro y recomiéndale cambiarla en "Mi Perfil".',
                ],
            },
            {
                titulo: 'Cambiar lo que puede hacer un rol (permisos)',
                pasos: [
                    'Entra a "Roles y Permisos", busca el rol y haz clic en "Editar" sobre su lista de permisos.',
                    'Marca o desmarca los permisos y guarda. Cada permiso controla una acción concreta: por ejemplo, "Ver reportes" permite abrir Reportes y "Editar afiliados" permite modificar un afiliado.',
                    'El cambio se aplica de inmediato: la opción desaparece del menú de esos usuarios y, si escriben la dirección a mano, el sistema les niega el acceso.',
                    'El rol Super Admin siempre tiene todos los permisos y no se puede modificar, para que nadie quede fuera del sistema por error.',
                    'Las cuentas y el rol de Super Admin solo las ve otro Super Admin: un Empleado con permiso de gestionar usuarios no las encuentra en la lista, ni puede elegir ese rol, ni abrirlas o inactivarlas.',
                    'Los cambios que hagas a los roles se conservan: no se pierden cuando se actualiza el sistema.',
                    'Solo el Super Admin puede crear roles, crear permisos o cambiar los permisos de un rol. Otros usuarios con el permiso "Ver roles y permisos" solo pueden consultarlos.',
                ],
            },
            {
                titulo: 'Desactivar un usuario',
                pasos: [
                    'Igual que con los afiliados, un usuario no se elimina: se inactiva desde el listado.',
                    'Un usuario inactivo no puede iniciar sesión.',
                ],
            },
        ],
    },
];
</script>

<template>
    <Head title="Manual de Usuario" />
    <AuthenticatedLayout>
        <template #header>
            <h2>Manual de Usuario</h2>
            <p>Cómo usar cada módulo del sistema, paso a paso</p>
        </template>

        <div class="man">
            <!-- Riel de navegación -->
            <aside class="man-rail">
                <p class="man-rail__title">Roles del sistema</p>
                <a href="#roles" class="man-rail__link" @click="setActive('roles')"
                    :class="{ 'man-rail__link--active': activeId === 'roles' }">Roles</a>

                <p class="man-rail__title">Módulos</p>
                <a v-for="s in secciones" :key="s.id" :href="`#${s.id}`" class="man-rail__link"
                    @click="setActive(s.id)" :class="{ 'man-rail__link--active': activeId === s.id }">
                    {{ s.titulo }}
                </a>
            </aside>

            <!-- Contenido -->
            <div class="man-content">
                <!-- Roles -->
                <section id="roles" class="man-section">
                    <div class="man-section__head">
                        <h3>Roles del sistema</h3>
                        <span class="man-badge man-badge--navy">Todos los roles</span>
                    </div>
                    <p class="man-intro">Lo que puedes ver y hacer depende del rol asignado a tu cuenta.</p>
                    <div class="man-roles">
                        <div v-for="r in roles" :key="r.badge" class="man-role" :class="`man-role--${r.color}`">
                            <span class="man-role__badge">{{ r.badge }}</span>
                            <h4>{{ r.titulo }}</h4>
                            <p>{{ r.texto }}</p>
                        </div>
                    </div>
                </section>

                <!-- Secciones por módulo -->
                <section v-for="s in secciones" :key="s.id" :id="s.id" class="man-section">
                    <div class="man-section__head">
                        <h3>{{ s.titulo }}</h3>
                        <span class="man-badge">{{ s.badge }}</span>
                    </div>
                    <p class="man-intro">{{ s.intro }}</p>
                    <Link v-if="s.ruta" :href="route(s.ruta)" class="man-goto">
                        {{ s.rutaLabel }}
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </Link>

                    <div v-for="(f, i) in s.flujos" :key="i" class="man-flujo">
                        <h4 class="man-flujo__title">{{ f.titulo }}</h4>
                        <ol class="man-steps">
                            <li v-for="(p, j) in f.pasos" :key="j">{{ p }}</li>
                        </ol>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.man {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 28px;
    align-items: start;
    font-family: 'DM Sans', sans-serif;
}

/* ── Riel ── */
.man-rail {
    position: sticky;
    top: 76px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 12px;
}

.man-rail__title {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    padding: 10px 10px 4px;
}

.man-rail__link {
    font-size: 0.85rem;
    color: #475569;
    text-decoration: none;
    padding: 7px 10px;
    border-radius: 8px;
    transition: background 0.15s, color 0.15s;
}

.man-rail__link:hover { background: #f1f5f9; color: #0a3d6b; }
.man-rail__link--active { background: #eef2ff; color: #0a3d6b; font-weight: 700; }

/* ── Contenido ── */
.man-content { display: flex; flex-direction: column; gap: 20px; min-width: 0; }

.man-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 26px;
    scroll-margin-top: 76px;
}

.man-section__head {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 8px;
}

.man-section__head h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: #0a3d6b;
    font-weight: 700;
}

.man-badge {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    padding: 3px 10px;
    border-radius: 99px;
    background: #f0f4f8;
    color: #475569;
}

.man-badge--navy { background: #dbeafe; color: #1e3a8a; }

.man-intro { font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 12px; }

.man-goto {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #1565a8;
    text-decoration: none;
    margin-bottom: 18px;
}
.man-goto svg { width: 15px; height: 15px; }
.man-goto:hover { text-decoration: underline; }

/* ── Tarjetas de rol ── */
.man-roles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
}

.man-role {
    border-radius: 12px;
    padding: 16px 18px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
}

.man-role h4 { font-size: 0.95rem; color: #1e293b; font-weight: 700; margin: 8px 0 4px; }
.man-role p { font-size: 0.82rem; color: #64748b; line-height: 1.5; }

.man-role__badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 99px;
    font-family: monospace;
}

.man-role--navy  .man-role__badge { background: #dbeafe; color: #1e3a8a; }
.man-role--blue  .man-role__badge { background: #e0f2fe; color: #075985; }
.man-role--green .man-role__badge { background: #dcfce7; color: #166534; }

/* ── Flujos / pasos ── */
.man-flujo { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
.man-flujo:first-of-type { margin-top: 4px; border-top: none; padding-top: 0; }

.man-flujo__title { font-size: 0.92rem; font-weight: 700; color: #1e293b; margin-bottom: 10px; }

.man-steps {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-left: 22px;
    list-style: decimal;
}

.man-steps li {
    font-size: 0.86rem;
    color: #334155;
    line-height: 1.55;
    padding-left: 4px;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .man { grid-template-columns: 1fr; }
    .man-rail { position: static; flex-direction: row; flex-wrap: wrap; }
    .man-rail__title { width: 100%; }
}

@media (max-width: 480px) {
    .man-section { padding: 18px 16px; }
}
</style>
