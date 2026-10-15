# PANDUAN SKENARIO 2 - MULTI-CONTAINER CRUD
## Materi: K3s Microservices Deployment | XII TJKT 1 | 50 Menit

---

## 📋 TUJUAN
- Memahami konsep **Microservices** dengan Kubernetes (K3s)
- Deploy aplikasi **CRUD Multi-Container** (Web App + Nginx Proxy + Database)
- Memahami **Service Networking**, **ConfigMap/Secret**, dan **Self-healing**
- Praktik Docker image build dan import ke K3s containerd

---

## 🏗️ ARSITEKTUR (4 Container)

### Container 1: MySQL Database
- **Image**: mysql:8.0
- **Service**: ClusterIP (internal)
- **Deployment**: 1 replica
- **Storage**: emptyDir (ephemeral)

### Container 2: PHP Web App CRUD
- **Image**: app-toko:latest (custom build)
- **Service**: ClusterIP (internal)
- **Deployment**: 2 replicas (high availability)
- **Env Vars**: DB_HOST, DB_USER, DB_PASS, DB_NAME (via ConfigMap)

### Container 3: Nginx Reverse Proxy
- **Image**: nginx-toko:latest (custom build)
- **Service**: NodePort (eksternal port 30080)
- **Deployment**: 1 replica
- **Function**: Reverse proxy ke PHP App

### Container 4: Utility
- **ConfigMap**: Environment variables untuk aplikasi
- **Secret**: Database credentials (password)
- **Namespace**: toko-lab (isolasi)

---

## ⏱️ LANGKAH-LANGKAH

### 1. Pindah ke Folder Skenario 2
```bash
cd ~/k3s-deployment-training/skenario-2
```

### 2. Build Docker Images
```bash
# Build image untuk PHP App
docker build -t app-toko:latest -f Dockerfile .

# Build image untuk Nginx Proxy
docker build -t nginx-toko:latest -f nginx.Dockerfile .
```

### 3. Verifikasi Images
```bash
docker images | grep toko
```
**Expected Output**: `app-toko` dan `nginx-toko` terdaftar

### 4. Save Images ke Tar File
```bash
docker save app-toko:latest -o app-toko.tar
docker save nginx-toko:latest -o nginx-toko.tar
```

### 5. Import Images ke K3s Containerd
```bash
sudo k3s ctr images import app-toko.tar
sudo k3s ctr images import nginx-toko.tar
```

### 6. Apply Kubernetes Manifests
```bash
# Namespace, Secret, dan ConfigMap
kubectl apply -f k8s/00-namespace-secret.yaml

# MySQL Database
kubectl apply -f k8s/01-db-deployment.yaml

# PHP Web App
kubectl apply -f k8s/02-app-deployment.yaml

# Nginx Reverse Proxy
kubectl apply -f k8s/03-nginx-proxy.yaml
```

### 7. Verifikasi Deployment
```bash
# Cek semua pod di namespace toko-lab
kubectl get pods -n toko-lab

# Cek services
kubectl get svc -n toko-lab

# Cek namespace
kubectl get ns
```
**Expected Output**:
- 3 pod: `mysql`, `app-toko` (2 replicas), `nginx-proxy`
- 3 service: `mysql-service`, `app-toko`, `nginx-proxy`

### 8. Cek Logs (jika ada error)
```bash
# Logs PHP App
kubectl logs -n toko-lab deployment/app-toko

# Logs MySQL
kubectl logs -n toko-lab deployment/mysql

# Logs Nginx
kubectl logs -n toko-lab deployment/nginx-proxy
```

### 9. Akses Aplikasi CRUD
Buka browser dan akses:
```
http://<IP-VM>:30080
```

**Login Default:**
- Username: `admin`
- Password: `123`

### 10. Uji Fitur CRUD
- **Dashboard**: Lihat statistik penjualan
- **Data Barang**: Tambah barang baru (dengan upload foto)
- **Kategori**: Tambah kategori baru
- **POS (Kasir)**: Lakukan transaksi penjualan
- **Laporan**: Lihat laporan penjualan per tanggal
- **Kelola User**: Tambah user baru (role: admin/kasir/gudang)

**Catatan Penting**: Database `toko_db`, 5 tabel (users, kategori, barang, penjualan, penjualan_detail), dan data dummy dibuat otomatis saat pertama akses!

---

## 🧪 PENGUJIAN SELF-HEALING

### 1. Simulasikan Pod Failure
```bash
# Hapus pod app-toko secara manual
kubectl delete pod -n toko-lab -l app=app-toko
```

### 2. Observasi Self-Healing
```bash
# Watch pod recreation secara real-time
kubectl get pods -n toko-lab -w
```
**Expected**: K3s otomatis membuat pod baru untuk menjaga 2 replicas

### 3. Hapus Pod MySQL
```bash
kubectl delete pod -n toko-lab -l app=mysql
```
**Expected**: Pod MySQL direcreate otomatis

### 4. Verifikasi Aplikasi Masih Berjalan
Refresh browser, pastikan aplikasi masih bisa diakses dan data tidak hilang.

### 5. Cek Resource Usage
```bash
kubectl top pods -n toko-lab
```

---

## 🔧 TROUBLESHOOTING

### Pod Status: ImagePullBackOff / ErrImagePull
**Cause**: Image belum di-import ke K3s
**Solution**:
```bash
docker save app-toko:latest -o app-toko.tar
sudo k3s ctr images import app-toko.tar
```

### Pod Status: CrashLoopBackOff
**Cause**: Aplikasi tidak bisa connect ke database
**Solution**:
```bash
kubectl logs -n toko-lab deployment/app-toko
kubectl logs -n toko-lab deployment/mysql
```
Pastikan ConfigMap dan Secret sudah benar:
```bash
kubectl describe configmap app-config -n toko-lab
kubectl describe secret db-secret -n toko-lab
```

### Tidak Bisa Akses Browser
**Cause**: Firewall memblokir NodePort
**Solution**:
```bash
sudo ufw allow 30080/tcp
```

### Database Connection Error
**Cause**: Env vars tidak terbaca atau DB belum siap
**Solution**:
1. Cek ConfigMap:
```bash
kubectl describe configmap app-config -n toko-lab
```
2. Tunggu MySQL pod ready:
```bash
kubectl wait --for=condition=ready pod -l app=mysql -n toko-lab --timeout=120s
```

### Upload Foto Gagal
**Cause**: Permission folder uploads
**Solution**:
Cek pod logs dan pastikan folder uploads ada permission write.

---

## 📚 KONSEP PENTING

### 1. Pod vs Deployment
- **Pod**: Satu instance container (ephemeral, bisa dihapus kapan saja)
- **Deployment**: Manajer pod dengan replicas, self-healing, rolling update

### 2. Service Types
- **ClusterIP**: Akses internal antar pod di cluster (default)
- **NodePort**: Akses eksternal via IP Node + Port (30000-32767)
- **LoadBalancer**: Akses eksternal dengan load balancer cloud (butuh cloud provider)

### 3. ConfigMap vs Secret
- **ConfigMap**: Konfigurasi non-sensitive (env vars, config files)
- **Secret**: Data sensitif (password, API keys, certificates) - encoded base64

### 4. Microservices Architecture
- **Web App**: PHP application logic (CRUD operations)
- **Nginx Proxy**: Reverse proxy untuk routing & load balancing
- **Database**: MySQL untuk persistensi data
- **Utility**: ConfigMap/Secret untuk environment configuration

### 5. K3s Image Management
- K3s menggunakan containerd (bukan Docker daemon)
- Images harus di-import via `k3s ctr images import`
- `imagePullPolicy: Never` untuk menggunakan local images

---

## 🚀 DEPLOYMENT OTOMATIS (OPSIONAL)

Jika ingin menggunakan script otomatis:
```bash
cd ~/k3s-deployment-training/skenario-2
chmod +x deploy-local.sh
./deploy-local.sh
```

Script ini akan:
1. Build Docker images
2. Save & import ke K3s containerd
3. Apply semua manifest
4. Tampilkan status deployment
5. Cleanup tar files

---

## 📝 CATATAN PENTING

1. **Jangan gunakan Cloudflare Tunnel** di lab ini untuk menghindari setup DNS/Tunnel yang memakan waktu
2. **Semua service berjalan lokal** di dalam K3s cluster, tidak tergantung cloud publik
3. **Auto-DB Initialization**: Aplikasi appToko otomatis membuat database & tabel saat pertama akses
4. **Resource Limitation**: VM dengan RAM 2-4 GB sudah cukup untuk praktikum ini
5. **Self-healing**: K3s akan otomatis recreate pod yang crash/hilang
6. **Namespace Isolation**: Semua resource berada di namespace `toko-lab` untuk isolasi

---

## 🧹 CLEANUP

### Hapus Semua Resource
```bash
kubectl delete namespace toko-lab
```

### Hapus Docker Images (opsional)
```bash
docker rmi app-toko:latest
docker rmi nginx-toko:latest
```

### Hapus Tar Files
```bash
rm -f app-toko.tar nginx-toko.tar
```

---

## ✅ CHECKLIST KELULUSAN

- [ ] Docker images app-toko dan nginx-toko berhasil dibuild
- [ ] Images berhasil di-import ke K3s containerd
- [ ] Namespace toko-lab berhasil dibuat
- [ ] Semua pod (MySQL, App 2 replicas, Nginx) status `Running`
- [ ] Semua service (ClusterIP dan NodePort) berhasil dibuat
- [ ] Aplikasi CRUD berhasil diakses di browser
- [ ] Login dengan admin/123 berhasil
- [ ] Fitur CRUD (barang, kategori, POS, laporan) berfungsi
- [ ] Self-healing terbukti saat pod dihapus
- [ ] ConfigMap dan Secret berhasil diterapkan
- [ ] Cleanup namespace berhasil dilakukan

---

**Selamat Praktikum! 🎓**
