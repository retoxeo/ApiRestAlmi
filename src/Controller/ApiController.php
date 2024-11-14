<?php

namespace App\Controller;

// Entidades
use App\Entity\Usuario;
use App\Entity\Tienda;
use App\Entity\Incidencia;
use App\Entity\Producto;
use App\Entity\FotosProducto;
use App\Entity\Consola;
use App\Entity\Videojuego;
use App\Entity\Genero;
use App\Entity\DispositivoMovil;
use App\Entity\Pedido;
use App\Entity\Estado;
use App\Entity\Departamento;
use App\Entity\FotosTienda;
use App\Entity\VideojuegosGenero;
use App\Entity\PedidoProducto;
use App\Entity\ProductoTienda;

// Repositorios
use App\Repository\UsuarioRepository;
use App\Repository\TiendaRepository;
use App\Repository\IncidenciaRepository;
use App\Repository\ProductoRepository;
use App\Repository\FotosProductoRepository;
use App\Repository\ConsolaRepository;
use App\Repository\VideojuegoRepository;
use App\Repository\GeneroRepository;
use App\Repository\DispositivoMovilRepository;
use App\Repository\PedidoRepository;
use App\Repository\EstadoRepository;
use App\Repository\DepartamentoRepository;
use App\Repository\FotosTiendaRepository;
use App\Repository\VideojuegosGeneroRepository;
use App\Repository\PedidoProductoRepository;
use App\Repository\ProductoTiendaRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class ApiController extends AbstractController
{
    private $usuarioRepository;
    private $entityManager;
    private $serializer;
    private $passwordEncoder;

    public function __construct(UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
    }
    #[Route('/xeo/registro_usuario', name: 'app_api_registro_usuario', methods:['POST'])]
    public function registroUsuario(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response {
        
        $data = json_decode($request->getContent(), true);
        $nombre = $data['nombre'] ?? null;
        $apellido1 = $data['apellido1'] ?? null;
        $apellido2 = $data['apellido2'] ?? null;
        $correo = $data['correo'] ?? null;
        $contrasena = $data['contrasena'] ?? null;

        if (!$nombre || !$apellido1 || !$apellido2 || !$correo || !$contrasena) {
            return $this->json(['message' => 'Todos los campos son obligatorios'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->usuarioRepository->findOneBy(['correo' => $correo])) {
            return $this->json(['message' => 'El correo ya está en uso'], Response::HTTP_CONFLICT);
        }

        $usuario = new Usuario();
        $usuario->setNombre($nombre);
        $usuario->setApellido1($apellido1);
        $usuario->setApellido2($apellido2);
        $usuario->setCorreo($correo);
        $usuario->setContrasena($contrasena);

        try {
            $entityManager->persist($usuario);
            $entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['message' => 'Error al registrar el usuario: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['message' => 'Usuario registrado exitosamente'], Response::HTTP_CREATED);
    }
    #[Route('/xeo/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UsuarioRepository $usuarioRepository): JsonResponse
    {
        // Obtiene el correo y la contraseña del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);
        $correo = $data['correo'] ?? null;
        $contrasena = $data['contrasena'] ?? null;

        // Verifica si ambos campos están presentes
        if (!$correo || !$contrasena) {
            return new JsonResponse(['error' => 'Correo y contraseña son requeridos'], 400);
        }

        // Autentica al usuario usando el repositorio
        $usuario = $usuarioRepository->login($correo, $contrasena);

        // Si el usuario es válido, devuelve la información del usuario
        if ($usuario) {
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'id' => $usuario->getId(),
                    'nombre' => $usuario->getNombre(),
                    'correo' => $usuario->getCorreo(),
                    'foto' => $usuario->getFoto(),
                    'apellido1' => $usuario->getApellido1(),
                    'apellido2' => $usuario->getApellido2(),
                    'telefono' => $usuario->getTelefono(),
                    'pais' => $usuario->getPais(),
                    'ciudad' => $usuario->getCiudad(),
                    'cp' => $usuario->getCp(),
                    'provincia' => $usuario->getProvincia(),
                    'calle' => $usuario->getCalle(),
                    'numero' => $usuario->getNumero(),
                ]
            ], 200);
        }

        // Si no, retorna un error de autenticación
        return new JsonResponse(['error' => 'Correo o contraseña incorrectos'], 401);
    }

    //Usuario
    #[Route('/xeo/fotos_usuario', name: 'app_api_fotos_usuario_create', methods:['POST'])]
    public function CreateFotoUsuario(Request $request, EntityManagerInterface $entityManager, UsuarioRepository $usuarioRepository): Response {
        
        $idUsuario = $request->request->get('id_usuario');
        $usuario = $usuarioRepository->find($idUsuario);
    
        if (!$usuario) {
            return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
    
        if (!$file) {
            return $this->json(['message' => 'Archivo no encontrado'], Response::HTTP_BAD_REQUEST);
        }
    
        $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/assets/usuarios';
        $fileName = uniqid() . '.' . $file->guessExtension();
    
        // Eliminar la foto anterior si existe
        $filesystem = new Filesystem();
        if ($usuario->getFoto()) {
            $oldFilePath = $this->getParameter('kernel.project_dir') . '/assets/usuarios/' . basename($usuario->getFoto());
            if ($filesystem->exists($oldFilePath)) {
                try {
                    $filesystem->remove($oldFilePath);
                } catch (IOExceptionInterface $exception) {
                    return $this->json(['message' => 'Error al eliminar la foto anterior: ' . $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                return $this->json(['message' => 'La foto anterior no existe en la ruta: ' . $oldFilePath], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    
        try {
            $file->move($uploadsDirectory, $fileName);
        } catch (FileException $e) {
            return $this->json(['message' => 'Error al subir el archivo: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    
        $fotoPath = 'https://retoxeo8.duckdns.org/assets/usuarios/' . $fileName;
        $usuario->setFoto($fotoPath);
    
        $entityManager->persist($usuario);
        $entityManager->flush();
    
        return $this->json(['message' => 'Foto del usuario creada exitosamente', 'path' => $fotoPath], Response::HTTP_CREATED);
    }

    #[Route('/xeo/usuario/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, $id): JsonResponse
    {
        $usuario = $this->usuarioRepository->find($id);
    
        if (!$usuario) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (isset($data['nombre'])) {
            $usuario->setNombre($data['nombre']);
        }
        if (isset($data['apellido1'])) {
            $usuario->setApellido1($data['apellido1']);
        }
        if (isset($data['apellido2'])) {
            $usuario->setApellido2($data['apellido2']);
        }
        if (isset($data['ciudad'])) {
            $usuario->setCiudad($data['ciudad']);
        }
        if (isset($data['provincia'])) {
            $usuario->setProvincia($data['provincia']);
        }
        if (isset($data['pais'])) {
            $usuario->setPais($data['pais']);
        }
        if (isset($data['cp'])) {
            $usuario->setCp($data['cp']);
        }
        if (isset($data['calle'])) {
            $usuario->setCalle($data['calle']);
        }
        if (isset($data['numero'])) {
            $usuario->setNumero($data['numero']);
        }
        if (isset($data['telefono'])) {
            $usuario->setTelefono($data['telefono']);
        }
        if (isset($data['newPassword']) && $data['newPassword'] === $data['confirmNewPassword']) {
            $usuario->setContrasena($data['newPassword']);
        }
    
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();
    
        $jsonUser = $this->serializer->serialize($usuario, 'json', ['groups' => 'user:read']);
    
        return new JsonResponse($jsonUser, 200, [], true);
    }

      #[Route('/xeo/usuarios', name: 'app_api_usuarios', methods:['GET'])]
    public function GetUsuarios(UsuarioRepository $usuarioRepository): Response
    {
        $usuarios = $usuarioRepository->findAll();
        $usuariosArray = [];

        foreach ($usuarios as $usuario) {
            $usuariosArray[] = [
                'id' => $usuario->getId(),
                'nombre' => $usuario->getNombre(),
                'apellido1' => $usuario->getApellido1(),
                'apellido2' => $usuario->getApellido2(),
                'correo' => $usuario->getCorreo(),
                'telefono' => $usuario->getTelefono(),
                'pais' => $usuario->getPais(),
                'provincia' => $usuario->getProvincia(),
                'ciudad' => $usuario->getCiudad(),
                'cp' => $usuario->getCp(),
                'calle' => $usuario->getCalle(),
                'numero' => $usuario->getNumero(),
                'foto' => $usuario->getFoto(),
                'contrasena' => $usuario->getContrasena(),
            ];
        }

        return new JsonResponse($usuariosArray, 200);
    }

    #[Route('/xeo/usuarios', name: 'app_api_usuarios_create', methods:['POST'])]
    public function CreateUsuario(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['nombre'], $data['apellido1'], $data['correo'], $data['telefono'], 
                    $data['pais'], $data['provincia'], $data['ciudad'], 
                    $data['calle'], $data['numero'], $data['contrasena'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $usuario = new Usuario();
        $usuario->setNombre($data['nombre']);
        $usuario->setApellido1($data['apellido1']);
        $usuario->setApellido2($data['apellido2']);
        $usuario->setCorreo($data['correo']);
        $usuario->setTelefono($data['telefono']);
        $usuario->setFoto($data['foto'] ?? ''); 
        $usuario->setPais($data['pais']);
        $usuario->setProvincia($data['provincia']);
        $usuario->setCp($data['cp']); 
        $usuario->setCiudad($data['ciudad']);
        $usuario->setCalle($data['calle']);
        $usuario->setNumero($data['numero']);
        $usuario->setContrasena($data['contrasena']);

        $entityManager->persist($usuario);
        $entityManager->flush(); 

        return $this->json(['message' => 'Usuario creado exitosamente'], Response::HTTP_CREATED);
    }

    // Tienda
    #[Route('/xeo/tiendas', name: 'app_api_tiendas', methods:['GET'])]
    public function GetTiendas(TiendaRepository $tiendaRepository, Request $request): Response
    {
        $tiendas = $tiendaRepository->findAll();

        $tiendasArray = [];

        foreach ($tiendas as $tienda) {
            $tiendasArray[] = [
                'id' => $tienda->getId(),
                'pais' => $tienda->getPais(),
                'provincia' => $tienda->getProvincia(),
                'cp' => $tienda->getCp(),
                'ciudad' => $tienda->getCiudad(),
                'calle' => $tienda->getCalle(),
                'numero' => $tienda->getNumero(),
                'telefono' => $tienda->getTelefono(),
                'correo' => $tienda->getCorreo(),
                'fotos' => array_map(function($foto) use ($request) {
                    return $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre();
                }, $tienda->getFotosTiendas()->toArray()),
            ];
        }

        return $this->convertToJson($tiendasArray);
    }

    #[Route('/xeo/tiendas', name: 'app_api_tiendas_create', methods:['POST'])]
    public function CreateTienda(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['pais'], $data['provincia'], $data['cp'], $data['ciudad'], 
                    $data['calle'], $data['numero'], $data['telefono'], $data['correo'])) {
            return $this->json(['message' => 'Faltan campos requeridos'], Response::HTTP_BAD_REQUEST);
        }
    
        $tienda = new Tienda();
        $tienda->setPais($data['pais']);
        $tienda->setProvincia($data['provincia']);
        $tienda->setCp($data['cp']);
        $tienda->setCiudad($data['ciudad']);
        $tienda->setCalle($data['calle']);
        $tienda->setNumero($data['numero']);
        $tienda->setTelefono($data['telefono']);
        $tienda->setCorreo($data['correo']);
    
        $entityManager->persist($tienda);
        $entityManager->flush();
    
        return $this->json(['message' => 'Tienda creada exitosamente'], Response::HTTP_CREATED);
    }
    
    // Incidencia
    #[Route('/xeo/incidencias', name: 'app_api_incidencias', methods:['GET'])]
    public function GetIncidencias(IncidenciaRepository $incidenciaRepository): Response
    {
        $incidencias = $incidenciaRepository->findAll();
        return $this->convertToJson($incidencias);
    }

    #[Route('/xeo/incidencias/{id}/editar', name: 'app_editar_incidencia')]
    public function showIncidenciaPage($id, IncidenciaRepository $incidenciaRepository)
    {
        // Buscar la incidencia por ID
        $incidencia = $incidenciaRepository->find($id);
    
        if (!$incidencia) {
            throw $this->createNotFoundException('Incidencia no encontrada');
        }
    
        // Pasar la incidencia al template para editarla
        return $this->render('api/index.html.twig', [
            'incidencia' => $incidencia, // Pasa la incidencia completa
        ]);
    }

    #[Route('/xeo/incidencias', name: 'app_api_incidencias_create', methods:['POST'])]
    public function CreateIncidencia(
        Request $request,
        EntityManagerInterface $entityManager,
        DepartamentoRepository $departamentoRepository,
        UsuarioRepository $usuarioRepository,
        EstadoRepository $estadoRepository
    ): Response {
        // Obtener los datos enviados desde el formulario
        $fechaInicio = $request->get('fechaInicio');
        $fechaFin = $request->get('fechaFin');
        $descripcion = $request->get('descripcion');
        $idUsuario = $request->get('idUsuario');
        $idDepartamento = 1;  // Asumimos que el departamento es TELEFONIA por defecto
    
        // Verificar si los campos requeridos están presentes
        if (!$fechaInicio || !$descripcion || !$idUsuario) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
    
        // Crear la nueva incidencia
        $incidencia = new Incidencia();
        $incidencia->setFechaInicio(new \DateTime($fechaInicio));
    
        // Si la fecha de fin no se proporcionó, dejarla como null
        if ($fechaFin) {
            $incidencia->setFechaFin(new \DateTime($fechaFin));
        } else {
            $incidencia->setFechaFin(null);  // O puedes dejarlo como null
        }
    
        // Establecer la descripción de la incidencia
        $incidencia->setDescripcion($descripcion);
    
        // Obtener el departamento, por defecto es TELEFONIA (idDepartamento = 1)
        $departamento = $departamentoRepository->find($idDepartamento);
        if ($departamento) {
            $incidencia->setDepartamento($departamento);
        }
    
        // Obtener el usuario por su ID
        $usuario = $usuarioRepository->find($idUsuario);
        if ($usuario) {
            $incidencia->setUsuario($usuario);
        } else {
            return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
    
        // Establecer el estado de la incidencia, por defecto "Pendiente"
        $estado = $estadoRepository->findOneBy(['nombre' => 'Pendiente']);
        if ($estado) {
            $incidencia->setEstado($estado);
        }
    
        // Persistir la nueva incidencia en la base de datos
        $entityManager->persist($incidencia);
        $entityManager->flush();
    
        return $this->json(['message' => 'Incidencia creada exitosamente'], Response::HTTP_CREATED);
    }
    
    

    #[Route('/xeo/incidencias/{id}/edit', name: 'app_api_incidencias_update', methods: ['POST'])]
    public function updateIncidencia(
        Request $request,
        EntityManagerInterface $entityManager,
        IncidenciaRepository $incidenciaRepository,
        $id
    ): Response {
        $incidencia = $incidenciaRepository->find($id);
        if (!$incidencia) {
            throw $this->createNotFoundException('No se encontró la incidencia');
        }
    
        // Obtener los valores del formulario
        $fechaFin = $request->get('fechaFin');
        $estado = $request->get('estado');
    
        // Actualizar solo los campos permitidos
        if ($fechaFin) {
            $incidencia->setFechaFin(new \DateTime($fechaFin));
        }
        if ($estado) {
            $estadoEntity = $entityManager->getRepository(Estado::class)->findOneBy(['nombre' => $estado]);
            if ($estadoEntity) {
                $incidencia->setEstado($estadoEntity);
            }
        }
    
        // Persistir los cambios
        $entityManager->flush();
    
        // Redirigir o mostrar mensaje de éxito
        return $this->redirectToRoute('app_api_index');
    }
    
    


    // Producto
    #[Route('/xeo/productos', name: 'app_api_productos', methods:['GET'])]
    public function GetProductos(ProductoRepository $productoRepository): Response
    {
        $productos = $productoRepository->findAll();
        return $this->convertToJson($productos);
    }

    #[Route('/xeo/productos', name: 'app_api_productos_create', methods: ['POST'])]
    public function CreateProducto(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Recibir datos del formulario
        $nombre = $request->get('nombre');
        $descripcion = $request->get('descripcion');
        $precio = $request->get('precio');
        $fechaLanzamiento = $request->get('fecha_lanzamiento');
        $desarrollador = $request->get('desarrollador');
        
        // Verificar los campos necesarios
        if (!$nombre || !$descripcion || !$precio || !$fechaLanzamiento || !$desarrollador) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        // Crear el nuevo producto
        $producto = new Producto();
        $producto->setNombre($nombre);
        $producto->setDescripcion($descripcion);
        $producto->setPrecio($precio);
        $producto->setFechaLanzamiento(new \DateTime($fechaLanzamiento)); 
        $producto->setDesarrollador($desarrollador);
        
        $entityManager->persist($producto);
        $entityManager->flush();

        // Devolver el ID del producto
        return $this->json([
            'message' => 'Producto creado exitosamente',
            'id' => $producto->getId()
        ], Response::HTTP_CREATED);
    }


    #[Route('/xeo/productos/{id}', name: 'app_api_productos_update', methods:['PUT'])]
    public function UpdateProducto(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductoRepository $productoRepository,
        $id
    ): Response {
        // Buscar el Producto por su ID
        $producto = $productoRepository->find($id);
        
        // Verificar si el Producto existe
        if (!$producto) {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);

        // Actualizar campos del Producto
        if (isset($data['nombre'])) {
            $producto->setNombre($data['nombre']);
        }
        if (isset($data['descripcion'])) {
            $producto->setDescripcion($data['descripcion']);
        }
        if (isset($data['precio'])) {
            $producto->setPrecio($data['precio']);
        }
        if (isset($data['fecha_lanzamiento'])) {
            $producto->setFechaLanzamiento(new \DateTime($data['fecha_lanzamiento']));
        }
        if (isset($data['desarrollador'])) {
            $producto->setDesarrollador($data['desarrollador']);
        }

        // Persistir los cambios en la base de datos
        $entityManager->persist($producto);
        $entityManager->flush(); // Guardar los cambios

        return $this->json(['message' => 'Producto actualizado exitosamente'], Response::HTTP_OK);
    }
    
    #[Route('/xeo/productos/{id}', name: 'app_api_productos_delete', methods:['DELETE'])]
    public function DeleteProducto(
        ProductoRepository $productoRepository,
        EntityManagerInterface $entityManager,
        $id
    ): Response {
        // Buscar el Producto por su ID
        $producto = $productoRepository->find($id);
        
        // Verificar si el Producto existe
        if (!$producto) {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Eliminar el producto
        $entityManager->remove($producto);
        $entityManager->flush(); // Guardar los cambios

        return $this->json(['message' => 'Producto eliminado exitosamente'], Response::HTTP_OK);
    }
    
    
    #[Route('/xeo/fotos_producto', name: 'upload_product_photo', methods: ['POST'])]
    public function uploadProductPhoto(Request $request, SluggerInterface $slugger): Response
    {
        try {
            $photoFile = $request->files->get('photo[]'); 

            if (!$photoFile) {
                throw new \Exception("No se ha recibido ninguna foto.");
            }

            $targetDirectory = $this->getParameter('kernel.project_dir') . '/assets/productos';
            
            $filesystem = new Filesystem();
            if (!$filesystem->exists($targetDirectory)) {
                $filesystem->mkdir($targetDirectory);
            }

            $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

            $photoFile->move($targetDirectory, $newFilename);

            return $this->json([
                'message' => 'Foto del producto creada exitosamente',
                'filename' => $newFilename
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Hubo un error al cargar la foto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    


    // Consola
    #[Route('/xeo/consolas', name: 'app_api_consolas', methods:['GET'])]
    public function GetConsolas(ConsolaRepository $consolaRepository, Request $request): Response
    {
        $consolas = $consolaRepository->findAll();
        
        $consolasArray = [];

        foreach ($consolas as $consola) {
            $stockTotal = 0;
            foreach ($consola->getProducto()->getProductoTiendas() as $productoTienda) {
                $stockTotal += $productoTienda->getStock();
            }

            $consolasArray[] = [
                'id_producto' => $consola->getProducto()->getId(),
                'id_consola' => $consola->getId(),
                'nombre' => $consola->getProducto()->getNombre(),
                'descripcion' => $consola->getProducto()->getDescripcion(),
                'precio' => $consola->getProducto()->getPrecio(),
                'almacenamiento' => $consola->getAlmacenamiento(),
                'fecha_lanzamiento' => $consola->getProducto()->getFechaLanzamiento(),
                'desarrollador' => $consola->getProducto()->getDesarrollador(),
                'fotos' => array_map(function($foto) use ($request) {
                    return $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre();
                }, $consola->getProducto()->getFotosProductos()->toArray()),
                'stock' => $stockTotal
            ];
        }

        return $this->json($consolasArray);
    }

    #[Route('/xeo/consolas', name: 'app_api_consolas_create', methods:['POST'])]
    public function CreateConsola(Request $request, ConsolaRepository $consolaRepository, ProductoRepository $productoRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
 
        if (!isset($data['almacenamiento'], $data['id_producto'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $consola = new Consola();
        $consola->setAlmacenamiento($data['almacenamiento']);

        $producto = $productoRepository->find($data['id_producto']);
        if ($producto) {
            $consola->setProducto($producto);
        } else {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->persist($consola);
        $entityManager->flush();

        return $this->json(['message' => 'Consola creada exitosamente'], Response::HTTP_CREATED);
    }

    #[Route('/xeo/consolas/{id}', name: 'app_api_consolas_update', methods:['PUT'])]
    public function UpdateConsola(
        $id,
        Request $request,
        ConsolaRepository $consolaRepository,
        ProductoRepository $productoRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Buscar la consola por su ID
        $consola = $consolaRepository->find($id);

        // Verificar si la consola existe
        if (!$consola) {
            return $this->json(['message' => 'Consola no encontrada'], Response::HTTP_NOT_FOUND);
        }

        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        // Verificar si los campos necesarios están presentes
        if (isset($data['almacenamiento'])) {
            $consola->setAlmacenamiento($data['almacenamiento']);
        }

        // Verificar si se proporcionó un ID de producto para actualizarlo
        if (isset($data['id_producto'])) {
            $producto = $productoRepository->find($data['id_producto']);
            if ($producto) {
                $consola->setProducto($producto);
            } else {
                return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
            }
        }

        // Persistir los cambios
        $entityManager->persist($consola);
        $entityManager->flush();

        return $this->json(['message' => 'Consola actualizada exitosamente'], Response::HTTP_OK);
    }


    //Videojuegos
    #[Route('/xeo/videojuegos', name: 'app_api_videojuegos', methods:['GET'])]
    public function GetVideojuegos(VideojuegoRepository $videojuegoRepository, Request $request): Response
    {
        $videojuegos = $videojuegoRepository->findAll();

        $videojuegosArray = [];
        
        foreach ($videojuegos as $videojuego) {
            $stockTotal = 0;
            foreach ($videojuego->getProducto()->getProductoTiendas() as $productoTienda) {
                $stockTotal += $productoTienda->getStock();
            }

            $videojuegosArray[] = [
                'id_producto' => $videojuego->getProducto()->getId(),
                'id_videojuego' => $videojuego->getId(),
                'nombre' => $videojuego->getProducto()->getNombre(),
                'descripcion' => $videojuego->getProducto()->getDescripcion(),
                'precio' => $videojuego->getProducto()->getPrecio(),
                'precio_alquiler' => $videojuego->getPrecio_alquiler(),
                'pegi' => $videojuego->getPegi(),
                'fecha_lanzamiento' => $videojuego->getProducto()->getFechaLanzamiento(),
                'desarrollador' => $videojuego->getProducto()->getDesarrollador(),
                'fotos' => array_map(function($foto) use ($request) {
                    return $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre();
                }, $videojuego->getProducto()->getFotosProductos()->toArray()),
                'generos' => $videojuego->getGeneros()->map(function($genero) {
                    return [
                        'id' => $genero->getId(),
                        'nombre' => $genero->getNombre(),
                    ];
                })->toArray(),
                'stock' => $stockTotal
            ];
        }

        return $this->json($videojuegosArray);
    }

    #[Route('/xeo/videojuegos', name: 'app_api_videojuegos_create', methods:['POST'])]
    public function CreateVideojuego(Request $request, VideojuegoRepository $videojuegoRepository, ProductoRepository $productoRepository, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['precio_alquiler'], $data['pegi'], $data['id_producto'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }
    
        $videojuego = new Videojuego();
        $videojuego->setPrecio_alquiler($data['precio_alquiler']);
        $videojuego->setPegi($data['pegi']);

        $producto = $productoRepository->find($data['id_producto']);
        if ($producto) {
            $videojuego->setProducto($producto);
        } else {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }
 
        $entityManager->persist($videojuego);
        $entityManager->flush(); 
    
        return $this->json(['message' => 'Videojuego creado exitosamente'], Response::HTTP_CREATED);
    }

    #[Route('/xeo/videojuegos/{id}', name: 'app_api_videojuegos_update', methods:['PUT'])]
    public function UpdateVideojuego(
        $id,
        Request $request,
        VideojuegoRepository $videojuegoRepository,
        ProductoRepository $productoRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Buscar el Videojuego por su ID
        $videojuego = $videojuegoRepository->find($id);

        // Verificar si el Videojuego existe
        if (!$videojuego) {
            return $this->json(['message' => 'Videojuego no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        // Verificar si los campos necesarios están presentes y actualizarlos
        if (isset($data['precio_alquiler'])) {
            $videojuego->setPrecio_alquiler($data['precio_alquiler']);
        }

        if (isset($data['pegi'])) {
            $videojuego->setPegi($data['pegi']);
        }

        // Verificar si se proporcionó un nuevo ID de producto
        if (isset($data['id_producto'])) {
            $producto = $productoRepository->find($data['id_producto']);
            if ($producto) {
                $videojuego->setProducto($producto);
            } else {
                return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
            }
        }

        // Persistir los cambios
        $entityManager->persist($videojuego);
        $entityManager->flush();

        return $this->json(['message' => 'Videojuego actualizado exitosamente'], Response::HTTP_OK);
    }

    #[Route('/xeo/videojuegos/{id}', name: 'app_api_videojuegos_delete', methods:['DELETE'])]
public function DeleteVideojuego(
    $id,
    VideojuegoRepository $videojuegoRepository,
    EntityManagerInterface $entityManager
): Response {
    // Buscar el Videojuego por su ID
    $videojuego = $videojuegoRepository->find($id);

    // Verificar si el Videojuego existe
    if (!$videojuego) {
        return $this->json(['message' => 'Videojuego no encontrado'], Response::HTTP_NOT_FOUND);
    }

    // Eliminar el videojuego de la base de datos
    $entityManager->remove($videojuego);
    $entityManager->flush();

    return $this->json(['message' => 'Videojuego eliminado exitosamente'], Response::HTTP_OK);
}

    
    // Genero
    #[Route('/xeo/generos', name: 'app_api_generos', methods:['GET'])]
    public function GetGeneros(GeneroRepository $generoRepository): Response
    {
        $generos = $generoRepository->findAll();

        $generosArray = [];

        foreach ($generos as $genero) {
            $generosArray[] = [
                'id' => $genero->getId(),
                'nombre' => $genero->getNombre(),
            ];
        }

        return $this->json($generosArray);
    }

    #[Route('/xeo/generos', name: 'app_api_generos_create', methods:['POST'])]
    public function CreateGenero(Request $request, EntityManagerInterface $entityManager): Response
    {

        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $genero = new Genero();
        $genero->setNombre($data['nombre']);

        $entityManager->persist($genero);
        $entityManager->flush();

        return $this->json(['message' => 'Género creado exitosamente'], Response::HTTP_CREATED);
    }


    // Dispositivo Móvil
    #[Route('/xeo/dispositivos_movil', name: 'app_api_dispositivos_movil', methods:['GET'])]
    public function GetDispositivosMovil(DispositivoMovilRepository $dispositivoMovilRepository, Request $request ): Response
    {
        $dispositivosMovil = $dispositivoMovilRepository->findAll();
        
        $dispositivosMovilArray = [];

        foreach ($dispositivosMovil as $dispositivoMovil) {
            $stockTotal = 0;
            foreach ($dispositivoMovil->getProducto()->getProductoTiendas() as $productoTienda) {
                $stockTotal += $productoTienda->getStock();
            }

            $dispositivosMovilArray[] = [
                'id_producto' => $dispositivoMovil->getProducto()->getId(),
                'id_dispositivo_movil' => $dispositivoMovil->getId(),
                'nombre' => $dispositivoMovil->getProducto()->getNombre(),
                'descripcion' => $dispositivoMovil->getProducto()->getDescripcion(),
                'precio' => $dispositivoMovil->getProducto()->getPrecio(),
                'sistema_operativo' => $dispositivoMovil->getSistemaOperativo(),
                'tipo' => $dispositivoMovil->getTipo(),
                'ram' => $dispositivoMovil->getRam(),
                'procesador' => $dispositivoMovil->getProcesador(),
                'almacenamiento' => $dispositivoMovil->getAlmacenamiento(),
                'fecha_lanzamiento' => $dispositivoMovil->getProducto()->getFechaLanzamiento(),
                'desarrollador' => $dispositivoMovil->getProducto()->getDesarrollador(),
                'fotos' => array_map(function($foto) use ($request) {
                    return $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre();
                }, $dispositivoMovil->getProducto()->getFotosProductos()->toArray()),
                'stock' => $stockTotal
            ];
        }

        return $this->json($dispositivosMovilArray);
    }

    #[Route('/xeo/dispositivos_movil', name: 'app_api_dispositivos_movil_create', methods:['POST'])]
    public function CreateDispositivoMovil(Request $request, EntityManagerInterface $entityManager, ProductoRepository $productoRepository): Response
    {

        $data = json_decode($request->getContent(), true);

        if (!isset($data['sistema_operativo'], $data['tipo'], $data['ram'], $data['procesador'], $data['almacenamiento'], $data['id_producto'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $dispositivoMovil = new DispositivoMovil();
        $dispositivoMovil->setSistemaOperativo($data['sistema_operativo']);
        $dispositivoMovil->setTipo($data['tipo']);
        $dispositivoMovil->setRam($data['ram']);
        $dispositivoMovil->setProcesador($data['procesador']);
        $dispositivoMovil->setAlmacenamiento($data['almacenamiento']);

        $producto = $productoRepository->find($data['id_producto']);
        if ($producto) {
            $dispositivoMovil->setProducto($producto);
        } else {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->persist($dispositivoMovil);
        $entityManager->flush();

        return $this->json(['message' => 'Dispositivo móvil creado exitosamente'], Response::HTTP_CREATED);
    }
    
    #[Route('/xeo/dispositivos_movil/{id}', name: 'app_api_dispositivos_movil_update', methods:['PUT'])]
    public function UpdateDispositivoMovil(
        $id,
        Request $request,
        DispositivoMovilRepository $dispositivoMovilRepository,
        ProductoRepository $productoRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Buscar el Dispositivo Móvil por su ID
        $dispositivoMovil = $dispositivoMovilRepository->find($id);

        // Verificar si el Dispositivo Móvil existe
        if (!$dispositivoMovil) {
            return $this->json(['message' => 'Dispositivo móvil no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obtener los datos del cuerpo de la solicitud
        $data = json_decode($request->getContent(), true);

        // Verificar y actualizar los campos proporcionados
        if (isset($data['sistema_operativo'])) {
            $dispositivoMovil->setSistemaOperativo($data['sistema_operativo']);
        }

        if (isset($data['tipo'])) {
            $dispositivoMovil->setTipo($data['tipo']);
        }

        if (isset($data['ram'])) {
            $dispositivoMovil->setRam($data['ram']);
        }

        if (isset($data['procesador'])) {
            $dispositivoMovil->setProcesador($data['procesador']);
        }

        if (isset($data['almacenamiento'])) {
            $dispositivoMovil->setAlmacenamiento($data['almacenamiento']);
        }

        // Verificar si se proporciona un nuevo ID de producto
        if (isset($data['id_producto'])) {
            $producto = $productoRepository->find($data['id_producto']);
            if ($producto) {
                $dispositivoMovil->setProducto($producto);
            } else {
                return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
            }
        }

        // Persistir los cambios
        $entityManager->persist($dispositivoMovil);
        $entityManager->flush();

        return $this->json(['message' => 'Dispositivo móvil actualizado exitosamente'], Response::HTTP_OK);
    }




    // Pedido
    #[Route('/xeo/pedidos', name: 'app_api_pedidos', methods:['GET'])]
    public function GetPedidos(PedidoRepository $pedidoRepository): Response
    {
        $pedidos = $pedidoRepository->findAll();

        $pedidosArray = [];

        foreach ($pedidos as $pedido) {
            $pedidosArray[] = [
                'id' => $pedido->getId(),
                'fecha_inicio' => $pedido->getFechaInicio()->format('Y-m-d H:i:s'),
                'fecha_fin' => $pedido->getFechaFin() ? $pedido->getFechaFin()->format('Y-m-d H:i:s') : null,
                'descripcion' => $pedido->getDescripcion(),
                'pais' => $pedido->getPais(),
                'provincia' => $pedido->getProvincia(),
                'cp' => $pedido->getCp(),
                'ciudad' => $pedido->getCiudad(),
                'calle' => $pedido->getCalle(),
                'numero' => $pedido->getNumero(),
                'usuario' => [
                    'id' => $pedido->getUsuario()->getId(),
                    'nombre' => $pedido->getUsuario()->getNombre(),
                    'apellido1' => $pedido->getUsuario()->getApellido1(),
                    'apellido2' => $pedido->getUsuario()->getApellido2(),
                    'correo' => $pedido->getUsuario()->getCorreo(),
                    'telefono' => $pedido->getUsuario()->getTelefono(),
                    'pais' => $pedido->getUsuario()->getPais(),
                    'provincia' => $pedido->getUsuario()->getProvincia(),
                    'cp' => $pedido->getUsuario()->getCp(),
                    'ciudad' => $pedido->getUsuario()->getCiudad(),
                    'calle' => $pedido->getUsuario()->getCalle(),
                    'numero' => $pedido->getUsuario()->getNumero(),
                ],
                'estado' => [
                    'id' => $pedido->getEstado()->getId(),
                    'nombre' => $pedido->getEstado()->getNombre(),
                ],
            ];
        }

        return $this->json($pedidosArray);
    }

    #[Route('/xeo/crearPedido', name: 'app_api_pedidos_create', methods:['POST'])]
    public function CreatePedido(Request $request, EntityManagerInterface $entityManager, UsuarioRepository $usuarioRepository, EstadoRepository $estadoRepository): Response {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['fecha_inicio'], $data['pais'], $data['provincia'], $data['cp'], 
                    $data['ciudad'], $data['calle'], $data['numero'], 
                    $data['id_usuario'], $data['id_estado'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $pedido = new Pedido();
        $pedido->setFechaInicio(new \DateTime($data['fecha_inicio']));

        if (isset($data['descripcion'])) {
            $pedido->setDescripcion($data['descripcion']);
        } else {
            $pedido->setDescripcion(null);
        }

        $pedido->setPais($data['pais']);
        $pedido->setProvincia($data['provincia']);
        $pedido->setCp($data['cp']);
        $pedido->setCiudad($data['ciudad']);
        $pedido->setCalle($data['calle']);
        $pedido->setNumero($data['numero']);

        $usuario = $usuarioRepository->find($data['id_usuario']);
        if ($usuario) {
            $pedido->setUsuario($usuario);
        } else {
            return $this->json(['message' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $estado = $estadoRepository->find($data['id_estado']);
        if ($estado) {
            $pedido->setEstado($estado);
        } else {
            return $this->json(['message' => 'Estado no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->persist($pedido);
        $entityManager->flush();

        return $this->json([
            'id_pedido' => $pedido->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('/xeo/pedidos/{id}/edit', name: 'app_api_pedidos_update', methods: ['POST'])]
    public function updatePedido(
        Request $request,
        EntityManagerInterface $entityManager,
        PedidoRepository $pedidoRepository,
        EstadoRepository $estadoRepository,
        $id
    ): Response {
        // Buscar el pedido por el ID
        $pedido = $pedidoRepository->find($id);
        if (!$pedido) {
            throw $this->createNotFoundException('No se encontró el pedido');
        }
    
        // Obtener los valores del formulario
        $fechaFin = $request->get('fechaFin');
        $estado = $request->get('estado');
    
        // Actualizar solo los campos permitidos (fecha_fin y estado)
        if ($fechaFin) {
            // Asegurarse de que se pase una fecha válida
            $pedido->setFechaFin(new \DateTime($fechaFin));
        }
    
        if ($estado) {
            // Buscar el estado por su nombre
            $estadoEntity = $estadoRepository->findOneBy(['nombre' => $estado]);
            if ($estadoEntity) {
                $pedido->setEstado($estadoEntity);
            } else {
                // Si el estado no existe, se puede devolver un error
                return $this->json(['message' => 'Estado no encontrado'], Response::HTTP_NOT_FOUND);
            }
        }
    
        // Persistir los cambios
        $entityManager->flush();
    
        // Devolver una respuesta exitosa
        return $this->json(['message' => 'Pedido actualizado exitosamente'], Response::HTTP_OK);
    }
    
    
    // Estado
    #[Route('/xeo/estados', name: 'app_api_estados', methods:['GET'])]
    public function GetEstados(EstadoRepository $estadoRepository): Response
    {
        $estados = $estadoRepository->findAll();
        return $this->convertToJson($estados);
    }

    #[Route('/xeo/estados', name: 'app_api_estados_create', methods:['POST'])]
    public function CreateEstado(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $estado = new Estado();
        $estado->setNombre($data['nombre']);

        $entityManager->persist($estado);
        $entityManager->flush(); 

        return $this->json(['message' => 'Estado creado exitosamente'], Response::HTTP_CREATED);
    }

    #[Route('/xeo/estados/{id}', name: 'app_api_estados_delete', methods:['DELETE'])]
    public function DeleteEstado($id, EntityManagerInterface $entityManager, EstadoRepository $estadoRepository): Response
    {
        $estado = $estadoRepository->find($id);

        if (!$estado) {
            return $this->json(['message' => 'Estado no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($estado);
        $entityManager->flush();

        return $this->json(['message' => 'Estado eliminado exitosamente'], Response::HTTP_OK);
    }


    // Departamento
    #[Route('/xeo/departamentos', name: 'app_api_departamentos', methods:['GET'])]
    public function GetDepartamentos(DepartamentoRepository $departamentoRepository): Response
    {
        $departamentos = $departamentoRepository->findAll();
        return $this->convertToJson($departamentos);
    }

    #[Route('/xeo/departamentos', name: 'app_api_departamentos_create', methods:['POST'])]
    public function CreateDepartamento(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre'])) {
            return $this->json(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $departamento = new Departamento();
        $departamento->setNombre($data['nombre']);

        $entityManager->persist($departamento);
        $entityManager->flush(); 

        return $this->json(['message' => 'Departamento creado exitosamente'], Response::HTTP_CREATED);
    }
    
    #[Route('/xeo/departamentos/{id}', name: 'app_api_departamentos_update', methods:['PUT'])]
    public function UpdateDepartamento(int $id, Request $request, EntityManagerInterface $entityManager, DepartamentoRepository $departamentoRepository): Response
    {

        $departamento = $departamentoRepository->find($id);

        if (!$departamento) {
            return $this->json(['message' => 'Departamento no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $departamento->setNombre($data['nombre']);
        } else {
            return $this->json(['message' => 'Nombre del departamento es requerido'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush(); 

        return $this->json(['message' => 'Departamento actualizado exitosamente'], Response::HTTP_OK);
    }


    // Foto_tienda
    #[Route('/xeo/fotos_tienda/{id}', name: 'app_api_fotos_tienda_get', methods:['GET'])]
    public function GetFotosTienda($id, Request $request, TiendaRepository $tiendaRepository): Response
    {
        $tienda = $tiendaRepository->find($id);

        if (!$tienda) {
            return $this->json(['message' => 'Tienda no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $fotos = [];
        foreach ($tienda->getFotosTiendas() as $foto) {
            $fotos[] = [
                'nombre' => $foto->getNombre(),
                'url' => $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre(), 
            ];
        }

        return $this->json(['fotos' => $fotos]);
    }

    #[Route('/xeo/fotos_tienda', name: 'app_api_fotos_tienda_create', methods:['POST'])]
    public function CreateFotoTienda(Request $request, EntityManagerInterface $entityManager, TiendaRepository $tiendaRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre']) || !isset($data['id_tienda'])) {
            return $this->json(['message' => 'Datos incompletos'], Response::HTTP_BAD_REQUEST);
        }

        $tienda = $tiendaRepository->find($data['id_tienda']);
        if (!$tienda) {
            return $this->json(['message' => 'Tienda no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $fotoTienda = new FotosTienda();
        $fotoTienda->setNombre($data['nombre']);
        $fotoTienda->setTienda($tienda);

        $entityManager->persist($fotoTienda);
        $entityManager->flush();

        return $this->json(['message' => 'Foto de tienda creada exitosamente'], Response::HTTP_CREATED);
    }

    // Videojuegos_Genero
    #[Route('/xeo/videojuego/genero/', name: 'app_api_videojuego_genero_create', methods:['POST'])]
    public function CreateVideojuegoGenero(Request $request, EntityManagerInterface $entityManager, VideojuegoRepository $videojuegoRepository, GeneroRepository $generoRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['id_videojuego'], $data['id_genero'])) {
            return $this->json(['message' => 'Datos incompletos'], Response::HTTP_BAD_REQUEST);
        }

        $videojuego = $videojuegoRepository->find($data['id_videojuego']);
        if (!$videojuego) {
            return $this->json(['message' => 'Videojuego no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $genero = $generoRepository->find($data['id_genero']);
        if (!$genero) {
            return $this->json(['message' => 'Género no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $videojuego->addGenero($genero);
        $entityManager->flush();

        return $this->json(['message' => 'Género añadido al videojuego exitosamente'], Response::HTTP_CREATED);
    }
    
    // Pedido_Producto
    #[Route('/xeo/pedidos_producto', name: 'app_api_pedidos_producto', methods:['GET'])]
    public function GetPedidosProducto(PedidoProductoRepository $pedidoProductoRepository): Response
    {
        $pedidosProducto = $pedidoProductoRepository->findAll();

        $pedidosProductoArray = [];

        foreach ($pedidosProducto as $pedidoProducto) {
            $pedidosProductoArray[] = [
                'id_pedido' => $pedidoProducto->getPedido()->getId(),
                'id_producto' => $pedidoProducto->getProducto()->getId(),
                'cantidad' => $pedidoProducto->getCantidad(),
                'precio_final' => $pedidoProducto->getPrecioFinal(),
                'precio_final_alquiler' => $pedidoProducto->getPrecioFinalAlquiler(),
            ];
        }

        return $this->json($pedidosProductoArray);
    }

    #[Route('/xeo/crearPedidoProducto', name: 'app_api_pedidos_producto_create', methods:['POST'])]
    public function CreatePedidoProducto(Request $request, EntityManagerInterface $entityManager, PedidoRepository $pedidoRepository, ProductoRepository $productoRepository): Response {
    
        $data = json_decode($request->getContent(), true);

        // Validar que al menos uno de los precios esté presente

        $pedidoProducto = new PedidoProducto();
        $pedidoProducto->setCantidad($data['cantidad']);
        
        // Asignar precios si están presentes
        $pedidoProducto->setPrecioFinal($data['precio_final'] ?? null);
        $pedidoProducto->setPrecioFinalAlquiler($data['precio_final_alquiler'] ?? null);

        $pedido = $pedidoRepository->find($data['id_pedido']);
        if (!$pedido) {
            return $this->json(['message' => 'Pedido no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $pedidoProducto->setPedido($pedido);

        $producto = $productoRepository->find($data['id_producto']);
        if (!$producto) {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $pedidoProducto->setProducto($producto);

        $entityManager->persist($pedidoProducto);
        $entityManager->flush();

        return $this->json(['message' => 'Producto en pedido creado exitosamente'], Response::HTTP_CREATED);
    }

    // Producto_Tienda
    #[Route('/xeo/productos_tienda', name: 'app_api_productos_tienda', methods:['GET'])]
    public function GetProductosTienda(ProductoTiendaRepository $productoTiendaRepository): Response
    {
        $productosTienda = $productoTiendaRepository->findAll();
        return $this->convertToJson($productosTienda);
    }

    #[Route('/xeo/productos_tienda', name: 'app_api_productos_tienda_create', methods:['POST'])]
    public function CreateProductoTienda(Request $request,EntityManagerInterface $entityManager, ProductoRepository $productoRepository,TiendaRepository $tiendaRepository): Response {
        
        $data = json_decode($request->getContent(), true);

        if (!isset($data['stock'], $data['id_producto'], $data['id_tienda'])) {
            return $this->json(['message' => 'Datos incompletos'], Response::HTTP_BAD_REQUEST);
        }

        $productoTienda = new ProductoTienda();
        $productoTienda->setStock($data['stock']);

        $producto = $productoRepository->find($data['id_producto']);
        if (!$producto) {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }
        $productoTienda->setProducto($producto);

        $tienda = $tiendaRepository->find($data['id_tienda']);
        if (!$tienda) {
            return $this->json(['message' => 'Tienda no encontrada'], Response::HTTP_NOT_FOUND);
        }
        $productoTienda->setTienda($tienda);

        $entityManager->persist($productoTienda);
        $entityManager->flush();

        return $this->json(['message' => 'Producto en tienda creado exitosamente'], Response::HTTP_CREATED);
    }

    #[Route('/xeo/productos_tienda/{id}', name: 'app_api_productos_tienda_update', methods:['PUT'])]
    public function UpdateProductoTienda(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductoTiendaRepository $productoTiendaRepository,
        ProductoRepository $productoRepository,
        TiendaRepository $tiendaRepository,
        $id
    ): Response {
        // Buscar el ProductoTienda por su ID
        $productoTienda = $productoTiendaRepository->find($id);
        
        // Verificar si el ProductoTienda existe
        if (!$productoTienda) {
            return $this->json(['message' => 'ProductoTienda no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);

        // Actualizar campos del ProductoTienda
        if (isset($data['stock'])) {
            $productoTienda->setStock($data['stock']);
        }

        // Relación con Producto (si se proporciona)
        if (isset($data['id_producto'])) {
            $producto = $productoRepository->find($data['id_producto']);
            if ($producto) {
                $productoTienda->setProducto($producto);
            } else {
                return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
            }
        }

        // Relación con Tienda (si se proporciona)
        if (isset($data['id_tienda'])) {
            $tienda = $tiendaRepository->find($data['id_tienda']);
            if ($tienda) {
                $productoTienda->setTienda($tienda);
            } else {
                return $this->json(['message' => 'Tienda no encontrada'], Response::HTTP_NOT_FOUND);
            }
        }

        // Persistir los cambios en la base de datos
        $entityManager->persist($productoTienda);
        $entityManager->flush(); // Guardar los cambios

        return $this->json(['message' => 'Producto en tienda actualizado exitosamente'], Response::HTTP_OK);
    }



    //PAGINA WEB

    // src/Controller/ApiController.php

#[Route('/inicio', name: 'app_api_index')]
public function index(
    EntityManagerInterface $entityManager,
    ProductoRepository $productoRepository,
    IncidenciaRepository $incidenciaRepository,
    PedidoRepository $pedidoRepository,
    ConsolaRepository $consolaRepository,
    DispositivoMovilRepository $dispositivoMovilRepository,
    VideojuegoRepository $videojuegoRepository,
    UsuarioRepository $usuarioRepository // Asegúrate de incluir el repositorio de Usuario
): Response {
    // Obtener el usuario autenticado
    $user = $this->getUser();
    $userRoles = $user->getRoles();

    // Determinar el departamento según el rol
    $departamentoId = null;
    if (in_array('ROLE_TELEFONIA', $userRoles)) {
        $departamentoId = 1;
    } elseif (in_array('ROLE_CONSOLAS', $userRoles)) {
        $departamentoId = 2;
    }

    // Consultar incidencias con su estado asociado
    $query = $entityManager->createQuery(
        'SELECT i, e 
        FROM App\Entity\Incidencia i
        LEFT JOIN i.estado e
        WHERE i.departamento = :departamentoId'
    )->setParameter('departamentoId', $departamentoId);
    $incidenciasConEstado = $query->getResult();

    // Obtener los productos y pedidos
    $productos = $productoRepository->findAll();
    $pedidos = $pedidoRepository->findAll();

    // Obtener los estados disponibles
    $estadoRepository = $entityManager->getRepository(Estado::class);
    $estados = $estadoRepository->findAll();

    // Obtener los usuarios disponibles
    $usuarios = $usuarioRepository->findAll(); // Obtener todos los usuarios

    // Clasificar productos por categoría
    $consolas = $consolaRepository->findBy(['producto' => 'consolas']);
    $videojuegos = $videojuegoRepository->findBy(['producto' => 'videojuegos']);
    $moviles = $dispositivoMovilRepository->findBy(['producto' => 'moviles']);

    // Generar productos con fotos
    $productosConFotos = [];
    foreach ($productos as $producto) {
        $fotos = [];
        $firstFoto = $producto->getFotosProductos()->first();
        
        if ($firstFoto) {
            $fotos[] = [
                'nombre' => $firstFoto->getNombre(),
                'url' => '/assets/productos/' . $firstFoto->getNombre(),
            ];
        }

        $productosConFotos[] = [
            'producto' => $producto,
            'fotos' => $fotos,
        ];
    }

    // Manejo de formulario para guardar nueva incidencia
    $request = Request::createFromGlobals();
    if ($request->isMethod('POST')) {
        // Crear una nueva incidencia
        $incidencia = new Incidencia();
        
        // Obtener los valores del formulario
        $estadoPendiente = $estadoRepository->findOneBy(['nombre' => 'Pendiente']);
        $departamentoTelefonia = $entityManager->getRepository(Departamento::class)->find(1); // Departamento de Telefonía
        
        $incidencia->setEstado($estadoPendiente);  // Estado "Pendiente"
        $incidencia->setDepartamento($departamentoTelefonia);  // Departamento "Telefonía"
        $incidencia->setDescripcion($request->get('descripcion'));
        $incidencia->setFechaInicio(new \DateTime($request->get('fechaInicio')));
        
        // Si la fecha de fin está vacía, asignar null
        $fechaFin = $request->get('fechaFin');
        if (empty($fechaFin)) {
            $incidencia->setFechaFin(null);
        } else {
            $incidencia->setFechaFin(new \DateTime($fechaFin));
        }

        // Asignar el usuario desde el formulario
        $usuario = $usuarioRepository->find($request->get('idUsuario'));
        $incidencia->setUsuario($usuario);

        // Persistir la incidencia
        $entityManager->persist($incidencia);
        $entityManager->flush();

        // Redirigir o mostrar mensaje de éxito
        return $this->redirectToRoute('app_api_index');
    }

    // Renderizar la vista con los datos obtenidos
    return $this->render('api/index.html.twig', [
        'productosConFotos' => $productosConFotos,
        'incidencias' => $incidenciasConEstado,
        'pedidos' => $pedidos,
        'estados' => $estados,
        'consolas' => $consolas,
        'videojuegos' => $videojuegos,
        'moviles' => $moviles,
        'usuarios' => $usuarios, // Pasar los usuarios al template
    ]);
}





    #[Route('/producto/{id}', name: 'producto_detalle')]
public function detalleProducto($id, ProductoRepository $productoRepository, ProductoTiendaRepository $productoTiendaRepository, ConsolaRepository $consolaRepository, VideojuegoRepository $videojuegoRepository, DispositivoMovilRepository $dispositivoMovilRepository, Request $request): Response
{
    // Obtener el producto por ID desde la base de datos
    $producto = $productoRepository->find($id);
    if (!$producto) {
        throw $this->createNotFoundException('Producto no encontrado');
    }

    // Obtener las fotos del producto directamente desde la base de datos
    $fotos = [];
    foreach ($producto->getFotosProductos() as $foto) {
        // Construir la URL de la foto
        $fotos[] = [
            'nombre' => $foto->getNombre(),
            'url' => $request->getSchemeAndHttpHost() . '/assets/productos/' . $foto->getNombre(),
        ];
    }

    // Obtener el stock del producto desde la tabla Producto_Tienda
    $productoTienda = $productoTiendaRepository->findOneBy(['producto' => $producto]);
    $stock = $productoTienda ? $productoTienda->getStock() : 0;

    // Obtener los datos adicionales del producto según el tipo
    $consola = $consolaRepository->findOneBy(['producto' => $id]);
    $videojuego = $videojuegoRepository->findOneBy(['producto' => $id]);
    $dispositivoMovil = $dispositivoMovilRepository->findOneBy(['producto' => $id]);

    // Devolver los datos a la vista
    return $this->render('api/producto.html.twig', [
        'producto' => $producto,
        'fotos' => $fotos,
        'stock' => $stock,  // Pasamos el stock al template
        'consola' => $consola,
        'videojuego' => $videojuego,
        'dispositivoMovil' => $dispositivoMovil,
    ]);
}

#[Route('/xeo/productos/{id}/eliminar-completo', name: 'app_api_productos_full_delete', methods: ['DELETE'])]
public function DeleteProductoCompleto(
    $id,
    VideojuegoRepository $videojuegoRepository,
    FotosProductoRepository $fotosProductoRepository,
    ProductoRepository $productoRepository,
    EntityManagerInterface $entityManager
): Response {
    try {
        // Paso 1: Buscar el Producto principal
        $producto = $productoRepository->find($id);

        if (!$producto) {
            return $this->json(['message' => 'Producto no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Paso 2: Eliminar Videojuego asociado, si existe
        $videojuego = $videojuegoRepository->findOneBy(['producto' => $producto]);
        if ($videojuego) {
            $entityManager->remove($videojuego);
        }

        // Paso 3: Eliminar Fotos asociadas
        $fotos = $fotosProductoRepository->findBy(['producto' => $producto]);
        foreach ($fotos as $foto) {
            $entityManager->remove($foto);
        }

        // Paso 4: Eliminar el Producto
        $entityManager->remove($producto);

        // Guardar todos los cambios en una única transacción
        $entityManager->flush();

        // Redirigir a la página principal de productos después de la eliminación
        return $this->redirectToRoute('app_productos_index');  // Asegúrate de tener esta ruta configurada

    } catch (\Exception $e) {
        // Si ocurre un error, capturamos la excepción y enviamos un mensaje adecuado
        return $this->json(['message' => 'Error al eliminar el producto: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


    private function convertToJson($data): JsonResponse
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $normalized = $serializer->normalize($data, null, [
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
            ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            }
        ]);

        $jsonContent = $serializer->serialize($normalized, 'json');

        return JsonResponse::fromJsonString($jsonContent, 200);
    }
}
