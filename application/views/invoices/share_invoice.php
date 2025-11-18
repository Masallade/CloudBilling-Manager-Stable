<div class="container mt-5">
        <h1>Share Invoice</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#shareModal">
            Share Invoice
        </button>

        <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel">Share Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Share this invoice via:</p>
                        <ul class="list-unstyled">
                            <li>
                                <a href="<?php echo $emailShareLink; ?>" class="btn btn-info btn-sm">
                                    <span class="fas fa-envelope"></span> Email
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo $whatsappShareLink; ?>" target="_blank" class="btn btn-success btn-sm">
                                    <span class="fab fa-whatsapp"></span> WhatsApp
                                </a>
                            </li>
                            <!-- Add more sharing options here -->
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
