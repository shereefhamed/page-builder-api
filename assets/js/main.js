jQuery(document).ready(function ($) {
    $('.copy-api-key').click(function () {
        const apiKey = $('#generated-api-key').html();
        navigator.clipboard.writeText(apiKey)
            .then(() => {
                alert('API key copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy text: ', err);
            });
    });

    $('#export').click(function (e) {
        e.preventDefault();
        var titles = [];
        var data = [];

        $('.dataTable th').each(function () {
            titles.push($(this).text());
        });

        $('.dataTable td').each(function () {
            data.push($(this).text());
        });

        var CSVString = prepCSVRow(titles, titles.length, '');
        CSVString = prepCSVRow(data, titles.length, CSVString);

        var downloadLink = document.createElement("a");
        var blob = new Blob(["\ufeff", CSVString]);
        var url = URL.createObjectURL(blob);
        downloadLink.href = url;
        downloadLink.download = "data.csv";

        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });

    function prepCSVRow(arr, columnCount, initial) {
        var row = ''; 
        var delimeter = ','; 
        var newLine = '\r\n'; 

        function splitArray(_arr, _count) {
            var splitted = [];
            var result = [];
            _arr.forEach(function (item, idx) {
                if ((idx + 1) % _count === 0) {
                    splitted.push(item);
                    result.push(splitted);
                    splitted = [];
                } else {
                    splitted.push(item);
                }
            });
            return result;
        }
        var plainArr = splitArray(arr, columnCount);

        plainArr.forEach(function (arrItem) {
            arrItem.forEach(function (item, idx) {
                row += item + ((idx + 1) === arrItem.length ? '' : delimeter);
            });
            row += newLine;
        });
        return initial + row;
    }
});