# Queue Management System for Healthcare

<img width="577" alt="idh" src="https://github.com/user-attachments/assets/c9361d70-0eda-404a-8ec2-55d73b3240d9" />

## International Conference on Applied and Pure Sciences, 2024  
Faculty of Science, University of Kelaniya, Sri Lanka  

### Abstract No: SO-09  
#### A Cost-Effective and Adaptable Queue Management System to Increase Efficiency in Patient Queue Management

### Authors:
- **Adhikari A. M. N. D. S.** (Department of Physics and Electronics, University of Kelaniya, Sri Lanka)
- **Gunarathna T. G. L.** (Department of Physics and Electronics, University of Kelaniya, Sri Lanka)
- **Bandara K. D. Y.** (Department of Physics and Electronics, University of Kelaniya, Sri Lanka)
- **Gunawardana K. D. B. H.** (Department of Physics and Electronics, University of Kelaniya, Sri Lanka)
- **Seneviratne J. A.** (Department of Physics and Electronics, University of Kelaniya, Sri Lanka)
- **Perera M. H. M. T. S.** (National Institute of Infectious Diseases, Sri Lanka)

---

## Introduction
Healthcare systems, especially in resource-limited environments like Sri Lanka, struggle with high patient volumes and limited resources. These challenges lead to extended wait times and decreased patient satisfaction. Our **Queue Management System (QMS)** aims to replace inefficient manual methods, enhance operational efficiency, and optimize patient flow in hospitals and clinics.

## Features
- **Patient, Doctor, and Admin Interfaces**
- **QR Code-based Check-in System**
- **Printed Queue Tokens**
- **Doctor Dashboard for Real-time Queue Management**
- **Admin Control for User Management and KPI Monitoring**
- **Secure Data Handling with AES-256-CBC Encryption**
- **Machine Learning-based Queue Time Prediction (Random Forest Regression)**
- **ESP32 Embedded Devices with OLED Display & LEDs**
- **Offline Mode Support with mDNS-based Local Network Connectivity**

## Technology Stack
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** SQLite3
- **Security:** AES-256-CBC Encryption
- **Machine Learning Algorithm:** Random Forest Regression
- **Hardware:** ESP32 with OLED Display & LEDs

## Performance Optimization
Through extensive testing and optimization, execution time was reduced from **22 seconds to 1.5 seconds** on a **1.4 million row dataset**, using:
- Batch Processing
- Database Indexing
- Algorithm Optimization

A **one-tailed t-test** confirmed the significant performance improvement:  
Without optimization (M = 21.84, SD = 1.16) vs. with optimization (M = 1.52, SD = 0.28); **t(43) = 107.76, p < 0.001**.

## Validation & Scalability
- **Validated on 10 years of sample data**
- **Tested in diverse healthcare environments**
- **Scalable from small clinics to large hospitals**
- **Ensures reliable functionality even in rural areas**

## Installation & Deployment
1. Clone the repository:
   ```sh
   git clone https://github.com/DeegayuA/idh.git
   ```
2. Set up a local server (e.g., XAMPP) and configure the database.
3. Deploy the project on a web server.
4. Integrate with ESP32 devices for offline functionality.

## Future Work
- Integration with **cloud-based healthcare systems**
- Improved **AI-driven queue predictions**
- Expansion for **multi-hospital support**

## Contact
For inquiries, reach out to **[adhikar-ps19094@stu.kln.ac.lk](mailto:adhikar-ps19094@stu.kln.ac.lk)**.

## License
This project is licensed under the **MIT License**.

---
**Keywords:** Healthcare Management, Machine Learning, Queue Optimization, Scalability, System Integration
