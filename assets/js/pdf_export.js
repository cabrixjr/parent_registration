/**
 * Admin Dashboard PDF Export Engine
 * Kibaha Secondary School Parent Register System
 */

function downloadPDF(tableId, filename) {
    if (typeof window.jspdf === 'undefined') {
        alert('PDF Library is still loading. Please try again in a moment.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    // Header Branding Colors (Kibaha Secondary Theme)
    const primaryColor = [0, 33, 71]; // Deep Navy (#002147)
    const goldColor = [212, 175, 55]; // Gold (#d4af37)

    // Document Title Header
    doc.setFont('Helvetica', 'bold');
    doc.setFontSize(16);
    doc.setTextColor(...primaryColor);
    doc.text("KIBAHA SECONDARY SCHOOL", 14, 15);

    // Decorative Accent Line
    doc.setDrawColor(...goldColor);
    doc.setLineWidth(0.8);
    doc.line(14, 18, 196, 18);

    // Document Details
    doc.setFont('Helvetica', 'normal');
    doc.setFontSize(10);
    doc.setTextColor(80, 80, 80);
    doc.text("Official Parent-Teacher Meeting Attendance Report", 14, 24);
    
    const formattedDate = new Date().toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' });
    doc.text(`Generated: ${formattedDate}`, 14, 29);

    // Generate Formatted Table
    doc.autoTable({
        html: '#' + tableId,
        startY: 34,
        theme: 'grid',
        headStyles: {
            fillColor: primaryColor,
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'left'
        },
        bodyStyles: {
            fontSize: 9,
            textColor: [30, 30, 30]
        },
        alternateRowStyles: {
            fillColor: [238, 245, 255] // Soft Ice Blue (#eef5ff)
        },
        margin: { left: 14, right: 14 }
    });

    // Add Signature Footer
    const finalY = doc.lastAutoTable.finalY + 20;
    if (finalY < 260) {
        doc.setFontSize(9);
        doc.setTextColor(0, 0, 0);
        doc.text("Class Teacher Signature: _______________________", 14, finalY);
        doc.text("Headmaster Stamp: _______________________", 120, finalY);
    }

    // Trigger Browser PDF Download
    doc.save(`${filename}.pdf`);
}