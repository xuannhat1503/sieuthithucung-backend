package com.sieuthithucung.common;

import jakarta.transaction.Transactional;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

@Transactional
public abstract class AbstractCrudService<E, ID, D> {

    private final JpaRepository<E, ID> repository;
    private final EntityDtoMapper<E, D> mapper;
    private final String resourceName;

    protected AbstractCrudService(
            JpaRepository<E, ID> repository,
            EntityDtoMapper<E, D> mapper,
            String resourceName
    ) {
        this.repository = repository;
        this.mapper = mapper;
        this.resourceName = resourceName;
    }

    public D create(D dto) {
        E entity = mapper.toEntity(dto);
        E saved = repository.save(entity);
        return mapper.toDto(saved);
    }

    public D update(ID id, D dto) {
        E existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException(resourceName + " not found with id: " + id));
        mapper.updateEntity(dto, existing);
        E saved = repository.save(existing);
        return mapper.toDto(saved);
    }

    public void delete(ID id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException(resourceName + " not found with id: " + id);
        }
        repository.deleteById(id);
    }

    public D findById(ID id) {
        E entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException(resourceName + " not found with id: " + id));
        return mapper.toDto(entity);
    }

    public Page<D> findAll(Pageable pageable) {
        return repository.findAll(pageable).map(mapper::toDto);
    }
}

