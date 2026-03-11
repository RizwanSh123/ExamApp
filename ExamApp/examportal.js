// Password generator
function generatePassword(length = 8) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let password = '';
    for (let i = 0; i < length; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
}

// Session management
function setSession(user) {
    localStorage.setItem('loggedInUser', JSON.stringify(user));
}
function clearSession() {
    localStorage.removeItem('loggedInUser');
}
function getSession() {
    return JSON.parse(localStorage.getItem('loggedInUser') || 'null');
}

// Handle form submission
const form = document.getElementById('studentForm');
const resultDiv = document.getElementById('result');

if (form && resultDiv) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const bulkFile = document.getElementById('bulk')?.files?.[0];
        if (bulkFile) {
            // Bulk upload
            const reader = new FileReader();
            reader.onload = function(event) {
                const csv = event.target.result;
                const lines = csv.split('\n');
                let output = '<h2>Created Student IDs (Bulk):</h2><ul>';
                lines.forEach(line => {
                    const [enrollment, department, year, semester] = line.split(',');
                    if (enrollment && department && year && semester) {
                        const password = generatePassword();
                        output += `<li>${enrollment} | ${department} | Year: ${year} | Sem: ${semester} | Password: <b>${password}</b></li>`;
                    }
                });
                output += '</ul>';
                resultDiv.innerHTML = output;
            };
            reader.readAsText(bulkFile);
        } else {
            // Single entry
            const enrollment = form.enrollment.value;
            const department = form.department.value;
            const year = form.year.value;
            const semester = form.semester.value;
            const password = generatePassword();
            resultDiv.innerHTML = `<h2>Created Student ID:</h2><p>${enrollment} | ${department} | Year: ${year} | Sem: ${semester} | Password: <b>${password}</b></p>`;
        };
    });
}

if (typeof openAddSubAdmin === 'function') window.openAddSubAdmin = openAddSubAdmin;
if (typeof saveSubAdmin === 'function') window.saveSubAdmin = saveSubAdmin;
if (typeof showModal === 'function') window.showModal = showModal;
if (typeof closeM === 'function') window.closeM = closeM;
if (typeof renderFaculty === 'function') window.renderFaculty = renderFaculty;
if (typeof deleteFaculty === 'function') window.deleteFaculty = deleteFaculty;
if (typeof facultyLogin === 'function') window.facultyLogin = facultyLogin;

// On page load, show correct screen
window.addEventListener('DOMContentLoaded', function() {
    const user = getSession();
    if (user) {
        if (user.role === 'Sub-Admin') {
            showS('s-subadmin-panel');
        } else {
            showS('s-faculty');
        }
    }

    // PDF Download for Results
    const btnResultPDF = document.getElementById('downloadResultPDF');
    if (btnResultPDF) {
        btnResultPDF.onclick = function() {
            const dept = document.getElementById('pdfDept').value;
            const subject = document.getElementById('pdfSubject').value;
            const rows = document.querySelectorAll('#resTbody tr');
            if (!window.jspdf?.jsPDF) return;
            const doc = new window.jspdf.jsPDF();
            doc.setFontSize(16);
            doc.text('Student Results', 10, 10);
            doc.setFontSize(12);
            const headers = ['Enrollment No', 'Subject', 'Semester', 'Result', 'Percentage'];
            let y = 20;
            doc.text(headers.join(' | '), 10, y);
            y += 8;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if ((dept === '' || cells[4]?.innerText === dept) && (subject === '' || cells[6]?.innerText === subject)) {
                    const enrollment = cells[1]?.innerText || '';
                    const subjectVal = cells[6]?.innerText || '';
                    const semester = cells[5]?.innerText || '';
                    const result = cells[13]?.innerText || '';
                    const percentage = cells[7]?.innerText || '';
                    const line = [enrollment, subjectVal, semester, result, percentage].join(' | ');
                    doc.text(line, 10, y);
                    y += 8;
                }
            });
            doc.save('student_results.pdf');
        };
    }
});

// Sub-Admin Save Function Stub
if (typeof window.saveSubAdmin !== 'function') {
    window.saveSubAdmin = function() {
        alert('Sub-Admin save functionality not implemented yet.');
    };
}
// Update facultyLogin to set session
window.facultyLogin = function() {
    const id = document.getElementById('fUser').value;
    const pass = document.getElementById('fPass').value;
    let facultyList = JSON.parse(localStorage.getItem('facultyList') || '[]');
    const user = facultyList.find(f => f.id === id && f.pass === pass);
    if (user) {
        document.getElementById('fErr').style.display = 'none';
        setSession(user);
        if (user.role === 'Sub-Admin') {
            showS('s-subadmin-panel');
        } else {
            showS('s-faculty');
        }
    } else {
        document.getElementById('fErr').style.display = 'block';
    }
}

// Update logout to clear session
window.logout = function() {
    clearSession();
    showS('s-land');
}
