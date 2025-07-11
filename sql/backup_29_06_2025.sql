PGDMP  
    3                }            visiobd2    17.4    17.4 p    Z           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                           false            [           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                           false            \           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                           false            ]           1262    57367    visiobd2    DATABASE     n   CREATE DATABASE visiobd2 WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'es-ES';
    DROP DATABASE visiobd2;
                     postgres    false            �            1259    57544    admin_categoria    TABLE     r   CREATE TABLE public.admin_categoria (
    administrador_id integer NOT NULL,
    categoria_id integer NOT NULL
);
 #   DROP TABLE public.admin_categoria;
       public         heap r       postgres    false            �            1259    57529    admin_contenido    TABLE     r   CREATE TABLE public.admin_contenido (
    administrador_id integer NOT NULL,
    contenido_id integer NOT NULL
);
 #   DROP TABLE public.admin_contenido;
       public         heap r       postgres    false            �            1259    57381    administrador    TABLE     �   CREATE TABLE public.administrador (
    id integer NOT NULL,
    nickname character varying(50) NOT NULL,
    "contraseña" text NOT NULL,
    correo_electronico character varying(100) NOT NULL
);
 !   DROP TABLE public.administrador;
       public         heap r       postgres    false            �            1259    57380    administrador_id_seq    SEQUENCE     �   CREATE SEQUENCE public.administrador_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 +   DROP SEQUENCE public.administrador_id_seq;
       public               postgres    false    219            ^           0    0    administrador_id_seq    SEQUENCE OWNED BY     M   ALTER SEQUENCE public.administrador_id_seq OWNED BY public.administrador.id;
          public               postgres    false    218            �            1259    57491    calificacion    TABLE     *  CREATE TABLE public.calificacion (
    id integer NOT NULL,
    fecha_de_calificacion timestamp without time zone NOT NULL,
    nota smallint NOT NULL,
    mensaje text,
    usuario_id uuid,
    contenido_id integer,
    CONSTRAINT calificacion_nota_check CHECK (((nota >= 1) AND (nota <= 10)))
);
     DROP TABLE public.calificacion;
       public         heap r       postgres    false            �            1259    57490    calificacion_id_seq    SEQUENCE     �   CREATE SEQUENCE public.calificacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 *   DROP SEQUENCE public.calificacion_id_seq;
       public               postgres    false    234            _           0    0    calificacion_id_seq    SEQUENCE OWNED BY     K   ALTER SEQUENCE public.calificacion_id_seq OWNED BY public.calificacion.id;
          public               postgres    false    233            �            1259    57403 	   categoria    TABLE     u  CREATE TABLE public.categoria (
    id integer NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text,
    padre_id integer,
    estado character varying(20) DEFAULT 'activa'::character varying NOT NULL,
    CONSTRAINT categoria_estado_check CHECK (((estado)::text = ANY ((ARRAY['activa'::character varying, 'inactiva'::character varying])::text[])))
);
    DROP TABLE public.categoria;
       public         heap r       postgres    false            �            1259    57402    categoria_id_seq    SEQUENCE     �   CREATE SEQUENCE public.categoria_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE public.categoria_id_seq;
       public               postgres    false    223            `           0    0    categoria_id_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE public.categoria_id_seq OWNED BY public.categoria.id;
          public               postgres    false    222            �            1259    57426 	   contenido    TABLE     �  CREATE TABLE public.contenido (
    id integer NOT NULL,
    titulo character varying(100) NOT NULL,
    autor character varying(100) NOT NULL,
    descripcion text,
    precio_original numeric(10,2) NOT NULL,
    estado character varying(20) DEFAULT 'disponible'::character varying NOT NULL,
    "tamaño_mb" numeric(8,2) NOT NULL,
    fecha_de_subida timestamp without time zone NOT NULL,
    numero_de_descargas integer,
    promedio_de_calificacion numeric,
    tipo_archivo_id integer,
    categoria_id integer,
    promocion_id integer,
    archivo character varying(255),
    CONSTRAINT contenido_estado_check CHECK (((estado)::text = ANY ((ARRAY['disponible'::character varying, 'no disponible'::character varying])::text[])))
);
    DROP TABLE public.contenido;
       public         heap r       postgres    false            �            1259    57425    contenido_id_seq    SEQUENCE     �   CREATE SEQUENCE public.contenido_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE public.contenido_id_seq;
       public               postgres    false    227            a           0    0    contenido_id_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE public.contenido_id_seq OWNED BY public.contenido.id;
          public               postgres    false    226            �            1259    57474    descarga    TABLE       CREATE TABLE public.descarga (
    id integer NOT NULL,
    fecha_de_compra timestamp without time zone NOT NULL,
    precio_pagado numeric(10,2) NOT NULL,
    aplica_descuento boolean NOT NULL,
    es_regalo boolean NOT NULL,
    usuario_id uuid,
    contenido_id integer
);
    DROP TABLE public.descarga;
       public         heap r       postgres    false            �            1259    57473    descarga_id_seq    SEQUENCE     �   CREATE SEQUENCE public.descarga_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 &   DROP SEQUENCE public.descarga_id_seq;
       public               postgres    false    232            b           0    0    descarga_id_seq    SEQUENCE OWNED BY     C   ALTER SEQUENCE public.descarga_id_seq OWNED BY public.descarga.id;
          public               postgres    false    231            �            1259    57419 	   promocion    TABLE     �   CREATE TABLE public.promocion (
    id integer NOT NULL,
    porcentaje_de_descuento numeric(5,2) NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date NOT NULL
);
    DROP TABLE public.promocion;
       public         heap r       postgres    false            �            1259    57418    promocion_id_seq    SEQUENCE     �   CREATE SEQUENCE public.promocion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE public.promocion_id_seq;
       public               postgres    false    225            c           0    0    promocion_id_seq    SEQUENCE OWNED BY     E   ALTER SEQUENCE public.promocion_id_seq OWNED BY public.promocion.id;
          public               postgres    false    224            �            1259    57452    ranking    TABLE     �   CREATE TABLE public.ranking (
    id integer NOT NULL,
    fecha_inicio date NOT NULL,
    numero_de_descargas integer NOT NULL
);
    DROP TABLE public.ranking;
       public         heap r       postgres    false            �            1259    57458    ranking_contenido    TABLE     n   CREATE TABLE public.ranking_contenido (
    ranking_id integer NOT NULL,
    contenido_id integer NOT NULL
);
 %   DROP TABLE public.ranking_contenido;
       public         heap r       postgres    false            �            1259    57451    ranking_id_seq    SEQUENCE     �   CREATE SEQUENCE public.ranking_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.ranking_id_seq;
       public               postgres    false    229            d           0    0    ranking_id_seq    SEQUENCE OWNED BY     A   ALTER SEQUENCE public.ranking_id_seq OWNED BY public.ranking.id;
          public               postgres    false    228            �            1259    57513    recarga    TABLE     �   CREATE TABLE public.recarga (
    id integer NOT NULL,
    monto numeric(10,2) NOT NULL,
    fecha_de_recarga timestamp without time zone NOT NULL,
    usuario_id uuid,
    administrador_id integer
);
    DROP TABLE public.recarga;
       public         heap r       postgres    false            �            1259    57512    recarga_id_seq    SEQUENCE     �   CREATE SEQUENCE public.recarga_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 %   DROP SEQUENCE public.recarga_id_seq;
       public               postgres    false    236            e           0    0    recarga_id_seq    SEQUENCE OWNED BY     A   ALTER SEQUENCE public.recarga_id_seq OWNED BY public.recarga.id;
          public               postgres    false    235            �            1259    57560    regala    TABLE     �   CREATE TABLE public.regala (
    id integer NOT NULL,
    donante_id uuid NOT NULL,
    receptor_id uuid NOT NULL,
    contenido_id integer NOT NULL,
    fecha_regalo timestamp without time zone DEFAULT now() NOT NULL
);
    DROP TABLE public.regala;
       public         heap r       postgres    false            �            1259    57559    regala_id_seq    SEQUENCE     �   CREATE SEQUENCE public.regala_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 $   DROP SEQUENCE public.regala_id_seq;
       public               postgres    false    240            f           0    0    regala_id_seq    SEQUENCE OWNED BY     ?   ALTER SEQUENCE public.regala_id_seq OWNED BY public.regala.id;
          public               postgres    false    239            �            1259    57394    tipoarchivo    TABLE     �   CREATE TABLE public.tipoarchivo (
    id integer NOT NULL,
    nombre_del_tipo character varying(10) NOT NULL,
    mimetype character varying(50) NOT NULL
);
    DROP TABLE public.tipoarchivo;
       public         heap r       postgres    false            �            1259    57393    tipoarchivo_id_seq    SEQUENCE     �   CREATE SEQUENCE public.tipoarchivo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 )   DROP SEQUENCE public.tipoarchivo_id_seq;
       public               postgres    false    221            g           0    0    tipoarchivo_id_seq    SEQUENCE OWNED BY     I   ALTER SEQUENCE public.tipoarchivo_id_seq OWNED BY public.tipoarchivo.id;
          public               postgres    false    220            �            1259    57368    usuario    TABLE     B  CREATE TABLE public.usuario (
    id uuid NOT NULL,
    nickname character varying(50) NOT NULL,
    "contraseña" text NOT NULL,
    correo_electronico character varying(100) NOT NULL,
    foto character varying(255) DEFAULT 'default.jpg'::character varying,
    fecha_de_registro timestamp without time zone NOT NULL
);
    DROP TABLE public.usuario;
       public         heap r       postgres    false            �            1259    57582 
   vistasaldo    VIEW     n  CREATE VIEW public.vistasaldo AS
 SELECT id AS usuario_id,
    ((COALESCE(( SELECT sum(r.monto) AS sum
           FROM public.recarga r
          WHERE (r.usuario_id = u.id)), (0)::numeric) - COALESCE(( SELECT sum(d.precio_pagado) AS sum
           FROM public.descarga d
          WHERE ((d.usuario_id = u.id) AND ((d.es_regalo = false) OR (d.contenido_id IS NOT NULL)))), (0)::numeric)) - COALESCE(( SELECT sum(d2.precio_pagado) AS sum
           FROM public.descarga d2
          WHERE ((d2.usuario_id = u.id) AND (d2.es_regalo = true) AND (d2.contenido_id IS NULL))), (0)::numeric)) AS saldo
   FROM public.usuario u;
    DROP VIEW public.vistasaldo;
       public       v       postgres    false    232    236    217    232    232    232    236            c           2604    57384    administrador id    DEFAULT     t   ALTER TABLE ONLY public.administrador ALTER COLUMN id SET DEFAULT nextval('public.administrador_id_seq'::regclass);
 ?   ALTER TABLE public.administrador ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    218    219    219            l           2604    57494    calificacion id    DEFAULT     r   ALTER TABLE ONLY public.calificacion ALTER COLUMN id SET DEFAULT nextval('public.calificacion_id_seq'::regclass);
 >   ALTER TABLE public.calificacion ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    234    233    234            e           2604    57406    categoria id    DEFAULT     l   ALTER TABLE ONLY public.categoria ALTER COLUMN id SET DEFAULT nextval('public.categoria_id_seq'::regclass);
 ;   ALTER TABLE public.categoria ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    222    223    223            h           2604    57429    contenido id    DEFAULT     l   ALTER TABLE ONLY public.contenido ALTER COLUMN id SET DEFAULT nextval('public.contenido_id_seq'::regclass);
 ;   ALTER TABLE public.contenido ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    226    227    227            k           2604    57477    descarga id    DEFAULT     j   ALTER TABLE ONLY public.descarga ALTER COLUMN id SET DEFAULT nextval('public.descarga_id_seq'::regclass);
 :   ALTER TABLE public.descarga ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    232    231    232            g           2604    57422    promocion id    DEFAULT     l   ALTER TABLE ONLY public.promocion ALTER COLUMN id SET DEFAULT nextval('public.promocion_id_seq'::regclass);
 ;   ALTER TABLE public.promocion ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    225    224    225            j           2604    57455 
   ranking id    DEFAULT     h   ALTER TABLE ONLY public.ranking ALTER COLUMN id SET DEFAULT nextval('public.ranking_id_seq'::regclass);
 9   ALTER TABLE public.ranking ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    229    228    229            m           2604    57516 
   recarga id    DEFAULT     h   ALTER TABLE ONLY public.recarga ALTER COLUMN id SET DEFAULT nextval('public.recarga_id_seq'::regclass);
 9   ALTER TABLE public.recarga ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    236    235    236            n           2604    57563 	   regala id    DEFAULT     f   ALTER TABLE ONLY public.regala ALTER COLUMN id SET DEFAULT nextval('public.regala_id_seq'::regclass);
 8   ALTER TABLE public.regala ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    239    240    240            d           2604    57397    tipoarchivo id    DEFAULT     p   ALTER TABLE ONLY public.tipoarchivo ALTER COLUMN id SET DEFAULT nextval('public.tipoarchivo_id_seq'::regclass);
 =   ALTER TABLE public.tipoarchivo ALTER COLUMN id DROP DEFAULT;
       public               postgres    false    220    221    221            U          0    57544    admin_categoria 
   TABLE DATA           I   COPY public.admin_categoria (administrador_id, categoria_id) FROM stdin;
    public               postgres    false    238   ^�       T          0    57529    admin_contenido 
   TABLE DATA           I   COPY public.admin_contenido (administrador_id, contenido_id) FROM stdin;
    public               postgres    false    237   {�       B          0    57381    administrador 
   TABLE DATA           X   COPY public.administrador (id, nickname, "contraseña", correo_electronico) FROM stdin;
    public               postgres    false    219   ��       Q          0    57491    calificacion 
   TABLE DATA           j   COPY public.calificacion (id, fecha_de_calificacion, nota, mensaje, usuario_id, contenido_id) FROM stdin;
    public               postgres    false    234   A�       F          0    57403 	   categoria 
   TABLE DATA           N   COPY public.categoria (id, nombre, descripcion, padre_id, estado) FROM stdin;
    public               postgres    false    223   7�       J          0    57426 	   contenido 
   TABLE DATA           �   COPY public.contenido (id, titulo, autor, descripcion, precio_original, estado, "tamaño_mb", fecha_de_subida, numero_de_descargas, promedio_de_calificacion, tipo_archivo_id, categoria_id, promocion_id, archivo) FROM stdin;
    public               postgres    false    227   8�       O          0    57474    descarga 
   TABLE DATA           }   COPY public.descarga (id, fecha_de_compra, precio_pagado, aplica_descuento, es_regalo, usuario_id, contenido_id) FROM stdin;
    public               postgres    false    232   �       H          0    57419 	   promocion 
   TABLE DATA           Y   COPY public.promocion (id, porcentaje_de_descuento, fecha_inicio, fecha_fin) FROM stdin;
    public               postgres    false    225   t�       L          0    57452    ranking 
   TABLE DATA           H   COPY public.ranking (id, fecha_inicio, numero_de_descargas) FROM stdin;
    public               postgres    false    229   ʧ       M          0    57458    ranking_contenido 
   TABLE DATA           E   COPY public.ranking_contenido (ranking_id, contenido_id) FROM stdin;
    public               postgres    false    230   �       S          0    57513    recarga 
   TABLE DATA           \   COPY public.recarga (id, monto, fecha_de_recarga, usuario_id, administrador_id) FROM stdin;
    public               postgres    false    236   �       W          0    57560    regala 
   TABLE DATA           Y   COPY public.regala (id, donante_id, receptor_id, contenido_id, fecha_regalo) FROM stdin;
    public               postgres    false    240   ��       D          0    57394    tipoarchivo 
   TABLE DATA           D   COPY public.tipoarchivo (id, nombre_del_tipo, mimetype) FROM stdin;
    public               postgres    false    221   ɫ       @          0    57368    usuario 
   TABLE DATA           k   COPY public.usuario (id, nickname, "contraseña", correo_electronico, foto, fecha_de_registro) FROM stdin;
    public               postgres    false    217   ?�       h           0    0    administrador_id_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('public.administrador_id_seq', 3, true);
          public               postgres    false    218            i           0    0    calificacion_id_seq    SEQUENCE SET     B   SELECT pg_catalog.setval('public.calificacion_id_seq', 19, true);
          public               postgres    false    233            j           0    0    categoria_id_seq    SEQUENCE SET     ?   SELECT pg_catalog.setval('public.categoria_id_seq', 16, true);
          public               postgres    false    222            k           0    0    contenido_id_seq    SEQUENCE SET     ?   SELECT pg_catalog.setval('public.contenido_id_seq', 43, true);
          public               postgres    false    226            l           0    0    descarga_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('public.descarga_id_seq', 48, true);
          public               postgres    false    231            m           0    0    promocion_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('public.promocion_id_seq', 5, true);
          public               postgres    false    224            n           0    0    ranking_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('public.ranking_id_seq', 1, false);
          public               postgres    false    228            o           0    0    recarga_id_seq    SEQUENCE SET     =   SELECT pg_catalog.setval('public.recarga_id_seq', 40, true);
          public               postgres    false    235            p           0    0    regala_id_seq    SEQUENCE SET     <   SELECT pg_catalog.setval('public.regala_id_seq', 10, true);
          public               postgres    false    239            q           0    0    tipoarchivo_id_seq    SEQUENCE SET     @   SELECT pg_catalog.setval('public.tipoarchivo_id_seq', 6, true);
          public               postgres    false    220            �           2606    57548 $   admin_categoria admin_categoria_pkey 
   CONSTRAINT     ~   ALTER TABLE ONLY public.admin_categoria
    ADD CONSTRAINT admin_categoria_pkey PRIMARY KEY (administrador_id, categoria_id);
 N   ALTER TABLE ONLY public.admin_categoria DROP CONSTRAINT admin_categoria_pkey;
       public                 postgres    false    238    238            �           2606    57533 $   admin_contenido admin_contenido_pkey 
   CONSTRAINT     ~   ALTER TABLE ONLY public.admin_contenido
    ADD CONSTRAINT admin_contenido_pkey PRIMARY KEY (administrador_id, contenido_id);
 N   ALTER TABLE ONLY public.admin_contenido DROP CONSTRAINT admin_contenido_pkey;
       public                 postgres    false    237    237            z           2606    57392 2   administrador administrador_correo_electronico_key 
   CONSTRAINT     {   ALTER TABLE ONLY public.administrador
    ADD CONSTRAINT administrador_correo_electronico_key UNIQUE (correo_electronico);
 \   ALTER TABLE ONLY public.administrador DROP CONSTRAINT administrador_correo_electronico_key;
       public                 postgres    false    219            |           2606    57390 (   administrador administrador_nickname_key 
   CONSTRAINT     g   ALTER TABLE ONLY public.administrador
    ADD CONSTRAINT administrador_nickname_key UNIQUE (nickname);
 R   ALTER TABLE ONLY public.administrador DROP CONSTRAINT administrador_nickname_key;
       public                 postgres    false    219            ~           2606    57388     administrador administrador_pkey 
   CONSTRAINT     ^   ALTER TABLE ONLY public.administrador
    ADD CONSTRAINT administrador_pkey PRIMARY KEY (id);
 J   ALTER TABLE ONLY public.administrador DROP CONSTRAINT administrador_pkey;
       public                 postgres    false    219            �           2606    57499    calificacion calificacion_pkey 
   CONSTRAINT     \   ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_pkey PRIMARY KEY (id);
 H   ALTER TABLE ONLY public.calificacion DROP CONSTRAINT calificacion_pkey;
       public                 postgres    false    234            �           2606    57501 5   calificacion calificacion_usuario_id_contenido_id_key 
   CONSTRAINT     �   ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_usuario_id_contenido_id_key UNIQUE (usuario_id, contenido_id);
 _   ALTER TABLE ONLY public.calificacion DROP CONSTRAINT calificacion_usuario_id_contenido_id_key;
       public                 postgres    false    234    234            �           2606    57412    categoria categoria_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_pkey PRIMARY KEY (id);
 B   ALTER TABLE ONLY public.categoria DROP CONSTRAINT categoria_pkey;
       public                 postgres    false    223            �           2606    57435    contenido contenido_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY public.contenido
    ADD CONSTRAINT contenido_pkey PRIMARY KEY (id);
 B   ALTER TABLE ONLY public.contenido DROP CONSTRAINT contenido_pkey;
       public                 postgres    false    227            �           2606    57479    descarga descarga_pkey 
   CONSTRAINT     T   ALTER TABLE ONLY public.descarga
    ADD CONSTRAINT descarga_pkey PRIMARY KEY (id);
 @   ALTER TABLE ONLY public.descarga DROP CONSTRAINT descarga_pkey;
       public                 postgres    false    232            �           2606    57424    promocion promocion_pkey 
   CONSTRAINT     V   ALTER TABLE ONLY public.promocion
    ADD CONSTRAINT promocion_pkey PRIMARY KEY (id);
 B   ALTER TABLE ONLY public.promocion DROP CONSTRAINT promocion_pkey;
       public                 postgres    false    225            �           2606    57462 (   ranking_contenido ranking_contenido_pkey 
   CONSTRAINT     |   ALTER TABLE ONLY public.ranking_contenido
    ADD CONSTRAINT ranking_contenido_pkey PRIMARY KEY (ranking_id, contenido_id);
 R   ALTER TABLE ONLY public.ranking_contenido DROP CONSTRAINT ranking_contenido_pkey;
       public                 postgres    false    230    230            �           2606    57457    ranking ranking_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY public.ranking
    ADD CONSTRAINT ranking_pkey PRIMARY KEY (id);
 >   ALTER TABLE ONLY public.ranking DROP CONSTRAINT ranking_pkey;
       public                 postgres    false    229            �           2606    57518    recarga recarga_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_pkey PRIMARY KEY (id);
 >   ALTER TABLE ONLY public.recarga DROP CONSTRAINT recarga_pkey;
       public                 postgres    false    236            �           2606    57566    regala regala_pkey 
   CONSTRAINT     P   ALTER TABLE ONLY public.regala
    ADD CONSTRAINT regala_pkey PRIMARY KEY (id);
 <   ALTER TABLE ONLY public.regala DROP CONSTRAINT regala_pkey;
       public                 postgres    false    240            �           2606    57401 +   tipoarchivo tipoarchivo_nombre_del_tipo_key 
   CONSTRAINT     q   ALTER TABLE ONLY public.tipoarchivo
    ADD CONSTRAINT tipoarchivo_nombre_del_tipo_key UNIQUE (nombre_del_tipo);
 U   ALTER TABLE ONLY public.tipoarchivo DROP CONSTRAINT tipoarchivo_nombre_del_tipo_key;
       public                 postgres    false    221            �           2606    57399    tipoarchivo tipoarchivo_pkey 
   CONSTRAINT     Z   ALTER TABLE ONLY public.tipoarchivo
    ADD CONSTRAINT tipoarchivo_pkey PRIMARY KEY (id);
 F   ALTER TABLE ONLY public.tipoarchivo DROP CONSTRAINT tipoarchivo_pkey;
       public                 postgres    false    221            t           2606    57379 &   usuario usuario_correo_electronico_key 
   CONSTRAINT     o   ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_correo_electronico_key UNIQUE (correo_electronico);
 P   ALTER TABLE ONLY public.usuario DROP CONSTRAINT usuario_correo_electronico_key;
       public                 postgres    false    217            v           2606    57377    usuario usuario_nickname_key 
   CONSTRAINT     [   ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_nickname_key UNIQUE (nickname);
 F   ALTER TABLE ONLY public.usuario DROP CONSTRAINT usuario_nickname_key;
       public                 postgres    false    217            x           2606    57375    usuario usuario_pkey 
   CONSTRAINT     R   ALTER TABLE ONLY public.usuario
    ADD CONSTRAINT usuario_pkey PRIMARY KEY (id);
 >   ALTER TABLE ONLY public.usuario DROP CONSTRAINT usuario_pkey;
       public                 postgres    false    217            �           2606    57549 5   admin_categoria admin_categoria_administrador_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.admin_categoria
    ADD CONSTRAINT admin_categoria_administrador_id_fkey FOREIGN KEY (administrador_id) REFERENCES public.administrador(id);
 _   ALTER TABLE ONLY public.admin_categoria DROP CONSTRAINT admin_categoria_administrador_id_fkey;
       public               postgres    false    219    4734    238            �           2606    57554 1   admin_categoria admin_categoria_categoria_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.admin_categoria
    ADD CONSTRAINT admin_categoria_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES public.categoria(id);
 [   ALTER TABLE ONLY public.admin_categoria DROP CONSTRAINT admin_categoria_categoria_id_fkey;
       public               postgres    false    238    223    4740            �           2606    57534 5   admin_contenido admin_contenido_administrador_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.admin_contenido
    ADD CONSTRAINT admin_contenido_administrador_id_fkey FOREIGN KEY (administrador_id) REFERENCES public.administrador(id);
 _   ALTER TABLE ONLY public.admin_contenido DROP CONSTRAINT admin_contenido_administrador_id_fkey;
       public               postgres    false    219    237    4734            �           2606    57539 1   admin_contenido admin_contenido_contenido_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.admin_contenido
    ADD CONSTRAINT admin_contenido_contenido_id_fkey FOREIGN KEY (contenido_id) REFERENCES public.contenido(id);
 [   ALTER TABLE ONLY public.admin_contenido DROP CONSTRAINT admin_contenido_contenido_id_fkey;
       public               postgres    false    227    4744    237            �           2606    57507 +   calificacion calificacion_contenido_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_contenido_id_fkey FOREIGN KEY (contenido_id) REFERENCES public.contenido(id);
 U   ALTER TABLE ONLY public.calificacion DROP CONSTRAINT calificacion_contenido_id_fkey;
       public               postgres    false    227    234    4744            �           2606    57502 )   calificacion calificacion_usuario_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuario(id);
 S   ALTER TABLE ONLY public.calificacion DROP CONSTRAINT calificacion_usuario_id_fkey;
       public               postgres    false    217    234    4728            �           2606    57413 !   categoria categoria_padre_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.categoria
    ADD CONSTRAINT categoria_padre_id_fkey FOREIGN KEY (padre_id) REFERENCES public.categoria(id);
 K   ALTER TABLE ONLY public.categoria DROP CONSTRAINT categoria_padre_id_fkey;
       public               postgres    false    223    223    4740            �           2606    57441 %   contenido contenido_categoria_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.contenido
    ADD CONSTRAINT contenido_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES public.categoria(id);
 O   ALTER TABLE ONLY public.contenido DROP CONSTRAINT contenido_categoria_id_fkey;
       public               postgres    false    227    4740    223            �           2606    57446 %   contenido contenido_promocion_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.contenido
    ADD CONSTRAINT contenido_promocion_id_fkey FOREIGN KEY (promocion_id) REFERENCES public.promocion(id);
 O   ALTER TABLE ONLY public.contenido DROP CONSTRAINT contenido_promocion_id_fkey;
       public               postgres    false    225    227    4742            �           2606    57436 (   contenido contenido_tipo_archivo_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.contenido
    ADD CONSTRAINT contenido_tipo_archivo_id_fkey FOREIGN KEY (tipo_archivo_id) REFERENCES public.tipoarchivo(id);
 R   ALTER TABLE ONLY public.contenido DROP CONSTRAINT contenido_tipo_archivo_id_fkey;
       public               postgres    false    227    4738    221            �           2606    57485 #   descarga descarga_contenido_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.descarga
    ADD CONSTRAINT descarga_contenido_id_fkey FOREIGN KEY (contenido_id) REFERENCES public.contenido(id);
 M   ALTER TABLE ONLY public.descarga DROP CONSTRAINT descarga_contenido_id_fkey;
       public               postgres    false    4744    232    227            �           2606    57480 !   descarga descarga_usuario_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.descarga
    ADD CONSTRAINT descarga_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuario(id);
 K   ALTER TABLE ONLY public.descarga DROP CONSTRAINT descarga_usuario_id_fkey;
       public               postgres    false    232    4728    217            �           2606    57468 5   ranking_contenido ranking_contenido_contenido_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.ranking_contenido
    ADD CONSTRAINT ranking_contenido_contenido_id_fkey FOREIGN KEY (contenido_id) REFERENCES public.contenido(id);
 _   ALTER TABLE ONLY public.ranking_contenido DROP CONSTRAINT ranking_contenido_contenido_id_fkey;
       public               postgres    false    227    230    4744            �           2606    57463 3   ranking_contenido ranking_contenido_ranking_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.ranking_contenido
    ADD CONSTRAINT ranking_contenido_ranking_id_fkey FOREIGN KEY (ranking_id) REFERENCES public.ranking(id);
 ]   ALTER TABLE ONLY public.ranking_contenido DROP CONSTRAINT ranking_contenido_ranking_id_fkey;
       public               postgres    false    230    4746    229            �           2606    57524 %   recarga recarga_administrador_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_administrador_id_fkey FOREIGN KEY (administrador_id) REFERENCES public.administrador(id);
 O   ALTER TABLE ONLY public.recarga DROP CONSTRAINT recarga_administrador_id_fkey;
       public               postgres    false    236    219    4734            �           2606    57519    recarga recarga_usuario_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.recarga
    ADD CONSTRAINT recarga_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuario(id);
 I   ALTER TABLE ONLY public.recarga DROP CONSTRAINT recarga_usuario_id_fkey;
       public               postgres    false    236    4728    217            �           2606    57577    regala regala_contenido_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.regala
    ADD CONSTRAINT regala_contenido_id_fkey FOREIGN KEY (contenido_id) REFERENCES public.contenido(id);
 I   ALTER TABLE ONLY public.regala DROP CONSTRAINT regala_contenido_id_fkey;
       public               postgres    false    4744    227    240            �           2606    57567    regala regala_donante_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.regala
    ADD CONSTRAINT regala_donante_id_fkey FOREIGN KEY (donante_id) REFERENCES public.usuario(id);
 G   ALTER TABLE ONLY public.regala DROP CONSTRAINT regala_donante_id_fkey;
       public               postgres    false    4728    217    240            �           2606    57572    regala regala_receptor_id_fkey    FK CONSTRAINT     �   ALTER TABLE ONLY public.regala
    ADD CONSTRAINT regala_receptor_id_fkey FOREIGN KEY (receptor_id) REFERENCES public.usuario(id);
 H   ALTER TABLE ONLY public.regala DROP CONSTRAINT regala_receptor_id_fkey;
       public               postgres    false    217    240    4728            U      x������ � �      T      x������ � �      B   �   x���1�0 й=3�b"nʠĨDM&.߂���k�������cdUC<�yl�ٮl&��{mv�kF������,���-��Ek�$�Y�c�uB4U��*�T>*C>_J����	3���GH�h�~`d��8��>����O)���_?      Q   �  x���M�!F�p�� �6��e�P M��H����l�zS�������h(:G�R >���A0�������x[�߷�z��F$�C'�pHr�2\F7G�)��`�t�(�51g���Rf�~q��)����g�j"�,@>�&���5��g������h�3(�G$�d�U[9s�0y�"��#�k��n��8���yDW%L	������_�x�s��ɦ3'DO�b�з����<��sy��ޖu��Ջ���
��r9�x�S���(L^%f$�W��&�bڳaݖ2Მ�S��:��[s��(���JϺ/�B@	_C�ڿv��C���$iz��E:�H�����e���s.}dʌ;�*L��D��]cT7fm�Ǭ#���xV>B��V'���H-���Xh�`
��^�k�l��z-��,�%�u�����\ �F@��1�#w���J�.%���\�F�8�L?�����B@      F   �   x����J�0�׷O�'����q���@IۋD�����V��j�BV��-�9Ln�<���pU���G$QM�S~í����a���زغ�am1�9�[���|��=/8����sOoѻ������:a��p}�J���5̹�=�٥v e	�+FܘD�v������	7��~m��a	�.�k�ޱY3��(�Q����R3����6|��ъ`�����Z�f��rWU�'����      J   �  x��ZK��ƹ]S��ͽ�j���c6.]E���%�U�T���@ &�W�!�.���w��?�/�i���5N�<M�{��n�ٻv]<�ŻvS|94{o�_�M뢏�"Ģk��U��?~�څ��P�����:�Ȃ�#L>%�)3��^
�P��fy��Q���m���B�TF&S�ʔ�R�x��)e�f���:r�yPv�~�z���P�C�O���v�͖c�ڽ��vh�v��	�}���$�R�#�Kyև�K>�K崤L��ڥ�!�N�P�F�i{R�ݺ�]�i{�����\14�H��=i�:��]
���2zg<;e|�K��Y
G�^�(<�G,/��`S,�h<�ݹҽG`����f�^����+��ZF�g�M�C�B�����c��ۦXu�f����=��O�M�*�����
/(��.W$�y��Xv�����Q�B]R���~6�%1N{��R�A�h7%5���0"�3T8�<Wc,��E]�m밫��W�Yn���?ĪxUu�io���ݲ�l=YTͺ����~�Wz�s���>�x���Q	H��r�ǜs��>�h�%7�.5)�h/�ciKC��$%W�U�����+~��8�u�ס��k�X��%����Wն�].�%��]Նʷ�2(dv�:v�AJ/��2)?��VEN�21�	!� >"c\Xc|ɓ+����1={]������5n�vob��?�������J����bL���u���_��f��?�����ؖ�Υ*���ʾcOO���4�����7���������q���w���n��}�6ו���M���c��VA4�Ad��,�4��oF]H�����V� �қhE
F�hq��MA4�WU��!��A¬Hun��[�����o˾mf��uh��0u}�B� ��g�ŗ,��������G�#]�_���S0B��d���H�K�� �������B��-���& �^%!�1(^	Ė�#ag�s��U��ױ�_~��J�����Mu�款x����:��G���z�1�}8�m=�^n��飇����֗�-$�F���h��KG����SmlbQ*���
��u��%SN���_e���>\�����S9���*vM[�߶͇�
Fv��~[ݸz�L31c'h9��^�\������k�,�����)M�P"�I+�&���12:Ng���0	 ����9(ױ[ý��u��7�w�#O�|�ݱ�� |�q>c��܅�K��Z�wd�Q�q˕��"�����y˄���wg9�h]�bQ��ob׵���x�e���~�ɦ<�xxRH�5�>W��B�ѓ�KY&"��`0#�����^p��"�\!�]��:2�[$*',{0n/v�8�=���b�-��Ku���R�^�jF�K�%���D���AF�l�g���i�f'�sN��0���w�O��!y`D	���y��.U�ˀa�pʀ�*���΀�������܍k~�hѱ/�+@���~�:T8�f��+��TM�����u�},��7w�aO�A�A]r@��ɳq�B1�z�e&��2�!Y|�&QE4O��0�A�e��s��5Xbx��^�pqh�ao��P`�L��G�A�j	�#��`�_JJ����K�u����R�г%�����aj'd��n ���e;�@��/�rf��5��!aNꦋkЛ9�fo��v�߼��rw���u�]�ϥ��K)���C�h�ri	k#ׁJᣦ�G��H����ݼ���/nb�)���Uq�����bFg��_�>{�/�#���OFh�~l���]d��f�7� �om�e.W����K����u��iqh�~�`�l����zz������]��L9�V�C���j~DYa�,1�32K[t$�3j�d>rjJ=K�SZ�მ��f�T����YVI�Q��uD%M(���a�[�Q��$���uy��z}�o��"��+��sy�Z4f'�E.c�\�i�OQ฼Xo�L�v�zug�N�D�����&J�`T+{����y�9پђ�<9���❫�?_Cn��? bo�C���� ʹd�j�M�w�֓h�ywe��󋩴�!�c����zϯ��M�u��֒�$��j��jI��rO��%)���浆�Jr(3&��	�&%��Ί�-���}�c&3���b���V����(���?��̴�q}>
6@�+��IG�6��WA[�c��2f�ӓbcy_��,f_B��8�c��l�R����q'
R�&T��GX�%_H�(���=�J�4�&�dICP�XE��E�V�l6y����6�⍃r���L>�n��^F45��N�k%��Ei�^ʥ�L��K%\YZ�"t�d����'\[L[&�v?�H���v�wc ����z�3�1L���c��ʥ�;>FB�Xoc*�"I��'Q�RN��Py=4�����ń;��j/�P�=\���1Ħ�=�Un��\���]Mt� �*�<��L�}Lބ��!9���:UKuDҤY
tNF�@/-�����!�����5�����@�?�!%�?���a��c]�V��9����Ë�����y$�$� Ȑ����'*��2R�7����$Bc�z�O��l0�u�C��WLLڽ�A�O�~�V%��`��~>YY�
�2H��e6�2*�6O,�±��RO�͜i���G8�ľ?~�(���	�I�䄽p�9�/'��$�X$aiD�^�2W)�4D
21�$��x�4�ձ�{�wk�?{����=�q��i�����5�G�n����ĉ���A�Y��E>������~گ���)����3wO�B�/zX��/����ʨ��ߴ@�_U��ko>�~|���q���Q���ÝEb�B�x�+y$�l�4p��Ia �������2	�5���|������H{7���:�,�����)>��,���?��j@ɤ�h�#v�X����(��+?������R>��[߈�-������P|�M�3z�����I��mfa�c�8�6��]�]+����"q�C�KO��n�斛������vr��G���n�:�yLS=�S+�?z���O?jUy�B�,>5F��!��Vq�!�,aY�G`vLR]34x�P�CG=�z��ѥ��^g��e����KX�zB%,���i�����-/
x����ZFq:*�{X�x���� ���A�Xt��h��3}�is�C�	A�WG���4ON9Μ��Y#s�+��"��F��B�\Oc���!4�@Z+�Erߙ�q_�q=����b�N��k9�~w����q�rw
�'rI(�������t������Fd�q�m�f�\3����,�(W�,��}���Y���DK��FZ��&�b�`q�B`(CR���y`�o7�	t,~�y��'&��8�0Y�n�]�T��RMUp7�����NJ=٧���P ��$��)z�sc4.#�/�!e�F%��]�yi,���NCJ��s��m�ʭ�vv�U�����u�Cf±޿<��%1U����xj�.v���{���;ǁ��YV��ZǶ��n���s��a��Y����MEە#��l�t��g���w�t��/���4�`��K)��	h�ƺ����a�9���D����Lyj�J^��� fO� ��]7�7�;������^���,����ɱrd���zA#Λ�0���iIS�������$"A����8���TC����
�U3{�E�����~;L$#�cw�ʅ�;c�wC��wm���T ��m����]����ø+:�M�e\3M�X�˧ϧ�"���#���'���_�YƬ5��>�R�%� ��i��ZiAwB��1Ӕ�<������?bӞ�g����'|vA�����F��<�nY��̧duRA�� ��D���}�E�V�# �x�{�0�^`��p���_̂��=�����	!���C׿,�<y�o�&��      O   ^  x���K��8E�x�;��-�+x0�����>�����T�0�S�ҹ\H#�H�/�+�J����-X-�-�r��(F>r�R�7��.*V� Z����JMRs-y����/*�� ⅄Qv���c� M˶�y��<������AF��	I��;���]����qP9����W�T���t7Y]�ĉ$2.o8!����R�q{ø�}�g�5�v�f{����f���d���
.������?����e�v�p���Qܮ�E8�jWk�+��!�@[�e�Ȃ�Мv�����)�^ �۝Ƒ�`XA�yy�ry ���D�a&{r�%ਆ��g���:�'<����Ӝ�����#�`��r֥�3��p^��=,��;�������j?��1:�,^��G�Y"�e��Վ9^	4��<�0����1�ˁF=:������x�Ï���Ro�7���=򪐌+��6�@~��T}��ܸ����㍆���v��#�Z�$<�~����h�Á�y�D�������1�Z��?i�_��G>ϣ�CN����ˣ7���M�^o�~t��U= ��m���z�pߩo�Á�`�u����ܳ��^�`��ږs�uڨ��<]�${n��~�:�������A�=��C��q%�������s6��Z�K<�m�帶S��!�u{�f��Jy�ˁG9�w9$�
��+L�x%�h��9Q!,�0�Z��-=]���=�W(�h�G_rL���F9��a�jտ�*ojXzy�7�7���<)"0z˖���^�(�����n���A>"X��+�p�r�ü�1�T��j�'#;��]�w%Z�5klr�
U9�q�,��B��FSN      H   F   x�u�Q
�0����.1�����?�@P����f�N��hMǓN�_Up�X���F�n9>�z��V���L(-�      L      x������ � �      M      x������ � �      S   {  x���͕� F�N� 	���W�lll�_��N8����H�~ ��l����d߁*����`�b&^��X�~���h��8������;�.����J�e�4Q�wI4��d�p�+�/�Ljm'�F��[��T�7���ѣ�Oz���Q�Y^ѐ��Y2e1WD��e�nU��hg�N���]W(6̃�/�����]�8���l�5�8u7� ���{�JM�-�STN�c�0gEEɲ�;D���׶̚�>X^�q��8�$��n�mS�v<��;��qK9�%��؇X81~��"g���(?`��&\��F��� ��9��ե�M���j�0��&���W�eX��?`V5/h�6���2� �u����1V���6O@���:l� ֪���z��<sQ�6O�`� Yw�� T-��f� R�[�/`� �UF��*�&`��P_[Qr���?XQ�-��Y�w�ᑳ\eM��ر��h�����,�T������������0��?`��:lb��i�p^�M�0ϽN���}��R��U׵e,�������\��㘥`�Ew���SX��R:?��ݯ�]X���"��]��0[�]��?�u��ұq��j����z�3z��      W   *  x��ӻ�-!�x��6���6S�&�@�%,��������-��.�E �'h���J0��ִ|/�x߁��@;T���âu���u�8��?����^�L��,��S��i�\��#'�/�<"7��d�3I���D�.ꃠ��a�n>ƌQ�WI��j*Q���W�C[� '��ƈ��Yz���~�(;����^�]+�?��l���w~�� q�3�,�oR�̈́k���+wz;(���1�+�պǻڴÙ�?$����l�Y�t4��R>(��j��3���P�r+G��K)�}���      D   f   x�3��
pu���MLO��*HM�2���	�ss�f&g�d�r�e������%@>�	�o�	T4�������X��	 �f��kUQ��kR����� ��#�      @     x�m�Ko�: F��Wtѭ��V70�@!<
���F�cCx�i��f��EYF������b�!��$��"��1�Yø�NXe�g����F�5��)�{|�G�>�t��q^�,ҙ>E����s��:%�m��j�BYY;�ŗ���$�~P\q���b��Mhw�����_\2o�V�*����E�!f r��d5*j�s�p�s�� 	/�w�<�PF�d2�0��^v��"��hߋ�!�q~X>�S�?��p��N/��Z���l���k?�o�vA�?覵R��7k^��Fd�'W0Ib!aTn^�����JH-�P�z~��_��{�8�{��ٶ"O�Y��]��`�g���o�(���:����y�r��:%���|q��#Ơ�pi��O\�2�(�М��@Kd�+/u�!�2����V�y��{/��8���wX�Y=छ��hs�=�z���t���:�禳�2�\�[ΤFq� �V�zC ��oh�5�z[bDYM�n]v�ں�������>��ȃ��~��U��a0����!�|u>N��-��l�2��_������TPz}C��!D� ^ќb�ʔ8��Z`(�ym�H�v�Q��{�xا.����ݲ��n��:�|[=����N:&:��ժ�Q���?:��bW���/�@g�^|�n�'\�2���;�5$C(YK�2�TBH��e*H)9��� F�M�`�˭�()���&�5���j���L�a��qXLO�F<*�s2���<���
x���P�#���\��	�Zy�@�#��a�R���u�     