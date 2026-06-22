# LicenseOS Demo Runbook

## Chuan bi truoc buoi demo

1. Bat Apache va MySQL trong XAMPP.
2. Import `database.sql`, sau do import `update.sql` neu may demo chua co database.
3. Import `config/demo_6_months_data.sql` de dua he thong ve dung trang thai kich ban.
4. Mo `http://localhost/Final_Project/public/`.

`demo_6_months_data.sql` co the import lai nhieu lan. File giu nguyen 600 nguoi dung va master data, chi tao lai du lieu van hanh.

## Kich ban trinh bay 8 phut

### 1. Dashboard van hanh

- Gioi thieu 6 khoa, 600 nguoi dung, 9 phan mem va 495 key.
- Chi ra 287 license active, 208 key con trong va bieu do nhu cau theo khoa.
- Mo bang license sap het han de chung minh dashboard lay du lieu that.

### 2. LicenseOS Assistant

- Hoi `Hien license qua han`.
- Tro ly phai phat hien 4 license qua han chua duoc xu ly.
- Hoi them `Phan mem nao sap het key?` de hien MATLAB het key, AutoCAD va Office gan can.
- Nhan manh chatbot chi chay intent/query da kiem soat va khong tra license key.

### 3. Nen tang va Master Data

- Cho xem moi khoa co 100 nguoi dung, email dung duoi `@vnu.edu.vn`.
- Demo tim kiem, phan trang 10 dong, modal chi tiet nguoi dung va CSV export.
- Gioi thieu rang khoa, nguoi dung va danh muc phan mem la du lieu dau vao cua toan bo workflow.

### 4. Luat cap phat

- Mo bang rule theo phan mem, khoa va vai tro.
- Giai thich backend chan cap trung va chi cap khi nguoi dung khop rule.

### 5. Kho license va Download Center

- Cho xem pool, key, ton kho va tai san cai dat cua 9 phan mem.
- Chi ra ba tinh huong: het key, sap het key va ton kho an toan.
- Download Center chi phan phoi asset, khong lam lo serial license.

### 6. Cap phat va vong doi tu dong

- Mo trang Cap phat. He thong tu dong doi 4 allocation qua han sang `Expired`.
- Transaction ghi notification/revocation log va tra key reusable ve kho.
- Demo cap mot key hop le, sau do thu hoi de cho thay ton kho duoc dong bo.

### 7. Kiem tra lai bang Copilot

- Quay lai Dashboard va hoi `Hien license qua han` mot lan nua.
- Ket qua phai ve 0 sau khi workflow tu dong xu ly.
- Ket luan bang audit log, thong ke sau thang va kha nang truy vet thao tac.

## Reset sau khi tap demo

Import lai `config/demo_6_months_data.sql`. Dashboard se tro ve 287 active va Copilot lai co 4 rui ro qua han de trinh bay tu dau.

